<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PDO;

class ImportSqliteDatabase extends Command
{
    protected $signature = 'db:import-sqlite
        {source : Path to the source SQLite database file}
        {--truncate : Truncate destination tables before import}
        {--skip=cache,cache_locks,sessions,jobs,job_batches,failed_jobs,password_reset_tokens,migrations : Comma-separated tables to skip}
        {--chunk=500 : Insert chunk size}
        {--force : Run in production without confirmation}';

    protected $description = 'Import data from a SQLite database file into the current database connection';

    public function handle(): int
    {
        $sourcePath = $this->argument('source');

        if (! str_starts_with($sourcePath, '/')) {
            $sourcePath = base_path($sourcePath);
        }

        if (! is_file($sourcePath)) {
            $this->error("SQLite source file not found: {$sourcePath}");
            return self::FAILURE;
        }

        if (app()->environment('production') && ! $this->option('force')) {
            $this->error('Refusing to import in production without --force.');
            return self::FAILURE;
        }

        if ($this->option('truncate') && ! $this->option('force')) {
            if (! $this->confirm('This will delete existing destination data. Continue?')) {
                return self::FAILURE;
            }
        }

        $source = new PDO('sqlite:'.$sourcePath);
        $source->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $source->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        $skipTables = collect(explode(',', (string) $this->option('skip')))
            ->map(fn (string $table) => trim($table))
            ->filter()
            ->values()
            ->all();

        $sourceTables = $this->sourceTables($source);
        $destinationTables = collect($sourceTables)
            ->filter(fn (string $table) => ! in_array($table, $skipTables, true))
            ->filter(fn (string $table) => Schema::hasTable($table))
            ->values()
            ->all();

        if ($destinationTables === []) {
            $this->warn('No matching tables found to import.');
            return self::SUCCESS;
        }

        $orderedTables = $this->orderTablesByDependencies($source, $destinationTables);

        $this->info('Destination connection: '.config('database.default'));
        $this->info('Source file: '.$sourcePath);
        $this->info('Tables: '.count($orderedTables));

        if ($this->option('truncate')) {
            $this->truncateTables($orderedTables);
        }

        $chunkSize = max(1, (int) $this->option('chunk'));
        $totalImported = 0;

        foreach ($orderedTables as $table) {
            $imported = $this->importTable($source, $table, $chunkSize);
            $totalImported += $imported;
            $this->line("{$table}: {$imported}");
        }

        $this->resetPostgresSequences($orderedTables);

        $this->info("Imported rows: {$totalImported}");

        return self::SUCCESS;
    }

    private function sourceTables(PDO $source): array
    {
        $rows = $source->query(
            "select name from sqlite_master where type = 'table' and name not like 'sqlite_%' order by name"
        )->fetchAll();

        return array_map(fn (array $row) => $row['name'], $rows);
    }

    private function orderTablesByDependencies(PDO $source, array $tables): array
    {
        $tableSet = array_fill_keys($tables, true);
        $dependencies = [];

        foreach ($tables as $table) {
            $dependencies[$table] = [];

            foreach ($source->query('pragma foreign_key_list("'.$this->sqliteIdentifier($table).'")')->fetchAll() as $foreignKey) {
                $foreignTable = $foreignKey['table'] ?? null;

                if ($foreignTable && $foreignTable !== $table && isset($tableSet[$foreignTable])) {
                    $dependencies[$table][$foreignTable] = true;
                }
            }
        }

        $ordered = [];
        $remaining = $dependencies;

        while ($remaining !== []) {
            $ready = array_keys(array_filter(
                $remaining,
                fn (array $deps) => $deps === []
            ));

            if ($ready === []) {
                $ready = [array_key_first($remaining)];
            }

            foreach ($ready as $table) {
                $ordered[] = $table;
                unset($remaining[$table]);

                foreach ($remaining as $remainingTable => $deps) {
                    unset($deps[$table]);
                    $remaining[$remainingTable] = $deps;
                }
            }
        }

        return $ordered;
    }

    private function importTable(PDO $source, string $table, int $chunkSize): int
    {
        $sourceColumns = $this->sourceColumns($source, $table);
        $destinationColumns = Schema::getColumnListing($table);
        $columns = array_values(array_intersect($destinationColumns, $sourceColumns));

        if ($columns === []) {
            return 0;
        }

        $quotedColumns = implode(', ', array_map(
            fn (string $column) => '"'.$this->sqliteIdentifier($column).'"',
            $columns
        ));

        $statement = $source->query('select '.$quotedColumns.' from "'.$this->sqliteIdentifier($table).'"');
        $buffer = [];
        $count = 0;

        while ($row = $statement->fetch()) {
            $buffer[] = array_intersect_key($row, array_fill_keys($columns, true));

            if (count($buffer) >= $chunkSize) {
                DB::table($table)->insert($buffer);
                $count += count($buffer);
                $buffer = [];
            }
        }

        if ($buffer !== []) {
            DB::table($table)->insert($buffer);
            $count += count($buffer);
        }

        return $count;
    }

    private function sourceColumns(PDO $source, string $table): array
    {
        $columns = $source->query('pragma table_info("'.$this->sqliteIdentifier($table).'")')->fetchAll();

        return array_map(fn (array $column) => $column['name'], $columns);
    }

    private function truncateTables(array $tables): void
    {
        if ($tables === []) {
            return;
        }

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('truncate table '.implode(', ', array_map([$this, 'postgresIdentifier'], $tables)).' restart identity cascade');
            return;
        }

        Schema::disableForeignKeyConstraints();

        foreach (array_reverse($tables) as $table) {
            DB::table($table)->truncate();
        }

        Schema::enableForeignKeyConstraints();
    }

    private function resetPostgresSequences(array $tables): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        foreach ($tables as $table) {
            if (! Schema::hasColumn($table, 'id')) {
                continue;
            }

            DB::statement(
                'select setval(pg_get_serial_sequence(?, ?), coalesce((select max("id") from '.$this->postgresIdentifier($table).'), 1), (select max("id") from '.$this->postgresIdentifier($table).') is not null)',
                [$table, 'id']
            );
        }
    }

    private function sqliteIdentifier(string $identifier): string
    {
        return str_replace('"', '""', $identifier);
    }

    private function postgresIdentifier(string $identifier): string
    {
        return '"'.str_replace('"', '""', $identifier).'"';
    }
}
