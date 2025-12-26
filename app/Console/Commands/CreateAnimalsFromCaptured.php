<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\OsvvRequest;
use App\Models\Animal;
use App\Models\AnimalStage;

class CreateAnimalsFromCaptured extends Command
{
    protected $signature = 'animals:create-from-captured';
    protected $description = 'Создать животных из заявок со статусом "отловлено" и поместить в карантин';

    public function handle()
    {
        $quarantineStage = AnimalStage::where('slug', 'quarantine')->first();
        
        if (!$quarantineStage) {
            $this->error('Этап "Карантин" не найден!');
            return 1;
        }
        
        // Получаем заявки со статусом "captured" без животных
        $capturedRequests = OsvvRequest::where('status', 'captured')
            ->whereDoesntHave('animal')
            ->get();
        
        if ($capturedRequests->isEmpty()) {
            $this->info('Нет заявок со статусом "отловлено" без животных');
            return 0;
        }
        
        $this->info("Найдено {$capturedRequests->count()} заявок для создания животных...");
        
        $created = 0;
        $errors = 0;
        
        foreach ($capturedRequests as $request) {
            try {
                $animal = Animal::create([
                    'osvv_request_id' => $request->id,
                    'type' => $request->animal_type ?? 'dog',
                    'gender' => $request->animal_gender ?? 'unknown',
                    'description' => $request->animal_description,
                    'current_stage_id' => $quarantineStage->id,
                    'status' => 'active',
                    'arrived_at' => $request->departure_date ?? $request->created_at,
                    'stage_started_at' => now(),
                ]);
                
                $created++;
                
                if ($created % 10 == 0) {
                    $this->info("Создано: {$created} животных");
                }
                
            } catch (\Exception $e) {
                $errors++;
                $this->error("Ошибка при создании животного для заявки #{$request->id}: " . $e->getMessage());
            }
        }
        
        $this->info("\nГотово!");
        $this->info("Создано животных: {$created}");
        $this->info("Ошибок: {$errors}");
        $this->info("Все животные помещены в этап: {$quarantineStage->name}");
        
        return 0;
    }
}
