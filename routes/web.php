<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\TaskController;

Route::redirect('/', '/dashboard');

Route::middleware(['auth'])->group(function () {

    Route::get('/dashboard', function () {
        $user = auth()->user();

        if ($user->isDirector()) {
            return view('director.dashboard');
        }

        $today   = now()->toDateString();
        $tasks   = \App\Models\Task::where('assigned_to', $user->id)
            ->where('status', 'new')
            ->orderByRaw("CASE priority WHEN 'high' THEN 0 WHEN 'medium' THEN 1 ELSE 2 END")
            ->get();

        $overdue   = $tasks->filter(fn($t) => $t->timing === 'date' && $t->due_date && $t->due_date->isPast());
        $todayT    = $tasks->filter(fn($t) => $t->timing === 'today');
        $laterT    = $tasks->filter(fn($t) => $t->timing === 'later');
        $scheduled = $tasks->filter(fn($t) => $t->timing === 'date' && (!$t->due_date || !$t->due_date->isPast()))
                           ->sortBy('due_date');

        return view('employee.dashboard', compact('overdue', 'todayT', 'laterT', 'scheduled'));
    })->name('dashboard');

    // Задачи
    Route::post('/tasks', [TaskController::class, 'store'])->name('tasks.store');
    Route::patch('/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');

    // Директор — API
    Route::get('/api/employees', [TaskController::class, 'employees'])->name('api.employees');
    Route::get('/api/employees/{user}/tasks', [TaskController::class, 'employeeTasks'])->name('api.employee.tasks');
});

require __DIR__.'/settings.php';
