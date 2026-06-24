<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Task;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Директор
        $director = User::updateOrCreate(
            ['email' => 'director@example.com'],
            [
                'name'     => 'Иван Директоров',
                'password' => bcrypt('password'),
                'role'     => 'director',
            ]
        );

        // Сотрудники
        $employees = [
            ['name' => 'Алексей Морозов',  'email' => 'alex@example.com'],
            ['name' => 'Мария Соколова',   'email' => 'maria@example.com'],
            ['name' => 'Дмитрий Петров',   'email' => 'dima@example.com'],
        ];

        foreach ($employees as $data) {
            $user = User::updateOrCreate(
                ['email' => $data['email']],
                ['name' => $data['name'], 'password' => bcrypt('password'), 'role' => 'employee']
            );

            // Тестовые задачи
            Task::create(['title' => 'Ежедневный отчёт',       'assigned_to' => $user->id, 'created_by' => $director->id, 'priority' => 'high',   'timing' => 'today']);
            Task::create(['title' => 'Проверить почту',         'assigned_to' => $user->id, 'created_by' => $director->id, 'priority' => 'medium', 'timing' => 'today']);
            Task::create(['title' => 'Обновить документацию',   'assigned_to' => $user->id, 'created_by' => $director->id, 'priority' => 'low',    'timing' => 'later']);
            Task::create(['title' => 'Встреча с командой',       'assigned_to' => $user->id, 'created_by' => $director->id, 'priority' => 'high',   'timing' => 'date', 'due_date' => now()->addDays(3)->toDateString()]);
        }

        // Просроченная задача
        $first = User::where('email', 'alex@example.com')->first();
        Task::create(['title' => 'Сдать квартальный отчёт', 'assigned_to' => $first->id, 'created_by' => $director->id, 'priority' => 'high', 'timing' => 'date', 'due_date' => now()->subDays(2)->toDateString()]);
    }
}
