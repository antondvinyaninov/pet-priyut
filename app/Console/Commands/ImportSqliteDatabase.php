<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PDO;
use Throwable;

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
        $chunkSize = max(1, (int) $this->option('chunk'));
        $totalImported = 0;

        $this->info('Destination connection: '.config('database.default'));
        $this->info('Source file: '.$sourcePath);
        $this->info('Tables: '.count($orderedTables));

        try {
            DB::transaction(function () use ($source, $orderedTables, $chunkSize, &$totalImported): void {
                if ($this->option('truncate')) {
                    $this->truncateTables($orderedTables);
                }

                foreach ($orderedTables as $table) {
                    $imported = $this->importTable($source, $table, $chunkSize);
                    $totalImported += $imported;
                    $this->line("{$table}: {$imported}");
                }

                $this->resetPostgresSequences($orderedTables);
            });
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());
            return self::FAILURE;
        }

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

        $columnMetadata = collect(Schema::getColumns($table))
            ->keyBy('name')
            ->all();

        $quotedColumns = implode(', ', array_map(
            fn (string $column) => '"'.$this->sqliteIdentifier($column).'"',
            $columns
        ));

        $statement = $source->query('select '.$quotedColumns.' from "'.$this->sqliteIdentifier($table).'"');
        $buffer = [];
        $count = 0;

        while ($sourceRow = $statement->fetch()) {
            $row = [];

            foreach ($columns as $column) {
                $row[$column] = $this->normalizeValue(
                    $table,
                    $column,
                    $sourceRow[$column] ?? null,
                    $columnMetadata[$column] ?? []
                );
            }

            $buffer[] = $row;

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

    private function normalizeValue(string $table, string $column, mixed $value, array $metadata): mixed
    {
        $value = $this->normalizeLegacyValue($table, $column, $value);
        $type = strtolower((string) ($metadata['type_name'] ?? $metadata['type'] ?? ''));

        if (is_string($value) && trim($value) === '') {
            return $this->blankValue($type, $metadata);
        }

        if ($value === null) {
            return null;
        }

        if ($this->isBooleanType($type)) {
            return $this->normalizeBoolean($value, $metadata);
        }

        if ($this->isIntegerType($type)) {
            return $this->normalizeInteger($value, $metadata);
        }

        if ($this->isNumericType($type)) {
            return $this->normalizeNumeric($value, $metadata);
        }

        if ($this->isJsonType($type)) {
            return $this->normalizeJson($value, $metadata);
        }

        if ($this->isDateTimeType($type)) {
            return $this->normalizeDateTime($value, $metadata);
        }

        return $value;
    }

    private function normalizeLegacyValue(string $table, string $column, mixed $value): mixed
    {
        $legacyValues = [
            'departure_plans.status' => [
                'planned' => 'draft',
            ],
            'departure_routes.status' => [
                'planned' => 'pending',
            ],
        ];

        return $legacyValues["{$table}.{$column}"][$value] ?? $value;
    }

    private function blankValue(string $type, array $metadata): mixed
    {
        if ((bool) ($metadata['nullable'] ?? true)) {
            return null;
        }

        $default = $this->defaultValue($metadata);

        if ($default !== null) {
            return $this->normalizeValue('', '', $default, $metadata);
        }

        if ($this->isBooleanType($type)) {
            return false;
        }

        if ($this->isIntegerType($type)) {
            return 0;
        }

        if ($this->isNumericType($type)) {
            return 0;
        }

        if ($this->isJsonType($type)) {
            return '{}';
        }

        return '';
    }

    private function normalizeBoolean(mixed $value, array $metadata): ?bool
    {
        if ($value === null) {
            return $this->defaultValue($metadata);
        }

        if (is_bool($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (bool) ((int) $value);
        }

        if (is_string($value)) {
            $normalized = strtolower(trim($value));

            return match ($normalized) {
                'true', 't', 'yes', 'y', 'on' => true,
                'false', 'f', 'no', 'n', 'off' => false,
                default => null,
            };
        }

        return null;
    }

    private function normalizeInteger(mixed $value, array $metadata): ?int
    {
        if ($value === null) {
            return $this->defaultValue($metadata);
        }

        if (is_int($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (int) round((float) $value);
        }

        return null;
    }

    private function normalizeNumeric(mixed $value, array $metadata): int|float|string|null
    {
        if ($value === null) {
            return $this->defaultValue($metadata);
        }

        if (is_numeric($value)) {
            return $value;
        }

        return null;
    }

    private function normalizeJson(mixed $value, array $metadata): ?string
    {
        if ($value === null) {
            return $this->defaultValue($metadata);
        }

        if (is_array($value) || is_object($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        if (is_string($value)) {
            $trimmed = trim($value);

            if ($trimmed === '') {
                return $this->blankValue('json', $metadata);
            }

            json_decode($trimmed);

            if (json_last_error() === JSON_ERROR_NONE) {
                return $trimmed;
            }

            return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private function normalizeDateTime(mixed $value, array $metadata): mixed
    {
        if ($value === null) {
            return $this->defaultValue($metadata);
        }

        if (is_string($value)) {
            $trimmed = trim($value);

            if ($trimmed === '' || str_starts_with($trimmed, '0000-00-00')) {
                return $this->blankValue('datetime', $metadata);
            }

            return $trimmed;
        }

        return $value;
    }

    private function defaultValue(array $metadata): mixed
    {
        $default = $metadata['default'] ?? null;

        if ($default === null) {
            return null;
        }

        $default = (string) $default;

        if (str_starts_with($default, 'nextval(')) {
            return null;
        }

        if (in_array(strtolower($default), ['true', 'false'], true)) {
            return strtolower($default) === 'true';
        }

        if (is_numeric($default)) {
            return str_contains($default, '.') ? (float) $default : (int) $default;
        }

        if (preg_match("/^'(.*)'::/", $default, $matches) || preg_match("/^'(.*)'$/", $default, $matches)) {
            return str_replace("''", "'", $matches[1]);
        }

        return null;
    }

    private function isBooleanType(string $type): bool
    {
        return in_array($type, ['bool', 'boolean'], true);
    }

    private function isIntegerType(string $type): bool
    {
        return in_array($type, ['int2', 'int4', 'int8', 'integer', 'bigint', 'smallint'], true);
    }

    private function isNumericType(string $type): bool
    {
        return in_array($type, ['numeric', 'decimal', 'float4', 'float8', 'double precision', 'real'], true);
    }

    private function isJsonType(string $type): bool
    {
        return in_array($type, ['json', 'jsonb'], true);
    }

    private function isDateTimeType(string $type): bool
    {
        return str_contains($type, 'date') || str_contains($type, 'time') || str_contains($type, 'timestamp');
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
