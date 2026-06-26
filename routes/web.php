<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\TaskController;

Route::redirect('/', '/dashboard');

Route::middleware(['auth'])->group(function () {

    Route::get('/dashboard', function () {
        $user = auth()->user();

        if ($user->isDirector()) {
            if (request('view') === 'tasks') {
                $tasks = \App\Models\Task::where('assigned_to', $user->id)
                    ->where('status', 'new')
                    ->orderByRaw("CASE priority WHEN 'high' THEN 0 WHEN 'medium' THEN 1 ELSE 2 END")
                    ->get();

                $today     = now()->startOfDay();
                $tomorrow  = now()->addDay()->startOfDay();
                $weekEnd   = now()->addDays(7)->startOfDay();

                $overdue   = $tasks->filter(fn($t) => $t->timing === 'date' && $t->due_date && $t->due_date->startOfDay()->lt($today));
                $todayT    = $tasks->filter(fn($t) => $t->timing === 'today' || ($t->timing === 'date' && $t->due_date && $t->due_date->startOfDay()->eq($today)));
                $tomorrowT = $tasks->filter(fn($t) => $t->timing === 'date' && $t->due_date && $t->due_date->startOfDay()->eq($tomorrow));
                $weekT     = $tasks->filter(fn($t) => $t->timing === 'date' && $t->due_date && $t->due_date->startOfDay()->gt($tomorrow) && $t->due_date->startOfDay()->lt($weekEnd));
                $laterT    = $tasks->filter(fn($t) => $t->timing === 'later' || ($t->timing === 'date' && $t->due_date && $t->due_date->startOfDay()->gte($weekEnd)));

                return view('director.tasks', compact('overdue', 'todayT', 'tomorrowT', 'weekT', 'laterT'));
            }
            if (!request()->has('view')) {
                return redirect()->route('dashboard', ['view' => 'tasks']);
            }


            return view('director.dashboard');
        }

        
        $tasks   = \App\Models\Task::where('assigned_to', $user->id)
            ->where('status', 'new')
            ->orderByRaw("CASE priority WHEN 'high' THEN 0 WHEN 'medium' THEN 1 ELSE 2 END")
            ->get();

        $today     = now()->startOfDay();
        $tomorrow  = now()->addDay()->startOfDay();
        $weekEnd   = now()->addDays(7)->startOfDay();

        $overdue   = $tasks->filter(fn($t) => $t->timing === 'date' && $t->due_date && $t->due_date->startOfDay()->lt($today));
        $todayT    = $tasks->filter(fn($t) => $t->timing === 'today' || ($t->timing === 'date' && $t->due_date && $t->due_date->startOfDay()->eq($today)));
        $tomorrowT = $tasks->filter(fn($t) => $t->timing === 'date' && $t->due_date && $t->due_date->startOfDay()->eq($tomorrow));
        $weekT     = $tasks->filter(fn($t) => $t->timing === 'date' && $t->due_date && $t->due_date->startOfDay()->gt($tomorrow) && $t->due_date->startOfDay()->lt($weekEnd));
        $laterT    = $tasks->filter(fn($t) => $t->timing === 'later' || ($t->timing === 'date' && $t->due_date && $t->due_date->startOfDay()->gte($weekEnd)));

        return view('employee.dashboard', compact('overdue', 'todayT', 'tomorrowT', 'weekT', 'laterT'));
    })->name('dashboard');

    // Задачи
    Route::post('/tasks', [TaskController::class, 'store'])->name('tasks.store');
    Route::patch('/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');

    // Директор — API
    Route::get('/api/employees', [TaskController::class, 'employees'])->name('api.employees');
    Route::get('/api/employees/{user}/tasks', [TaskController::class, 'employeeTasks'])->name('api.employee.tasks');

    

});


require __DIR__.'/settings.php';
