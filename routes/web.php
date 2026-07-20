<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\WorkObjectController;
use App\Models\WorkObject;

Route::redirect('/', '/dashboard');

Route::middleware(['auth'])->group(function () {

    Route::get('/dashboard', function () {
        $user = auth()->user();

        if (request('view') === 'team') {
            return view('director.dashboard');
        }

        if (request('view') === 'objects') {
            $objects = WorkObject::with('items')->orderBy('name')->get();

            return view('objects.index', compact('objects'));
        }

        $allTasks = \App\Models\Task::where('assigned_to', $user->id)
            ->orderByRaw("CASE priority WHEN 'high' THEN 0 WHEN 'medium' THEN 1 ELSE 2 END")
            ->get();
        $tasks = $allTasks->where('status', 'new');

        $today     = now()->startOfDay();
        $tomorrow  = now()->addDay()->startOfDay();
        $weekEnd   = now()->addDays(7)->startOfDay();

        $overdue   = $tasks->filter(fn($t) => $t->timing === 'date' && $t->due_date && $t->due_date->startOfDay()->lt($today));
        $todayT    = $tasks->filter(fn($t) => $t->timing === 'today' || ($t->timing === 'date' && $t->due_date && $t->due_date->startOfDay()->eq($today)));
        $tomorrowT = $tasks->filter(fn($t) => $t->timing === 'date' && $t->due_date && $t->due_date->startOfDay()->eq($tomorrow));
        $weekT     = $tasks->filter(fn($t) => $t->timing === 'date' && $t->due_date && $t->due_date->startOfDay()->gt($tomorrow) && $t->due_date->startOfDay()->lt($weekEnd));
        $laterT    = $tasks->filter(fn($t) => $t->timing === 'later' || ($t->timing === 'date' && $t->due_date && $t->due_date->startOfDay()->gte($weekEnd)));
        $todayAll  = $allTasks->filter(fn($t) => $t->timing === 'today' || ($t->timing === 'date' && $t->due_date && $t->due_date->startOfDay()->eq($today)));
        $todayDone = $todayAll->where('status', 'done')->count();
        $todayTotal = $todayAll->count();

        $tasksView = $user->isDirector() ? 'director.tasks' : 'employee.dashboard';

        return view($tasksView, compact('overdue', 'todayT', 'tomorrowT', 'weekT', 'laterT', 'todayDone', 'todayTotal'));
    })->name('dashboard');

    // Задачи
    Route::post('/tasks', [TaskController::class, 'store'])->name('tasks.store');
    Route::patch('/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');

    // Директор — API
    Route::get('/api/employees', [TaskController::class, 'employees'])->name('api.employees');
    Route::get('/api/employees/{user}/tasks', [TaskController::class, 'employeeTasks'])->name('api.employee.tasks');

    // Объекты
    Route::post('/objects', [WorkObjectController::class, 'store'])->name('objects.store');
    Route::post('/objects/{workObject}/items', [WorkObjectController::class, 'storeItem'])->name('objects.items.store');
    Route::patch('/object-items/{objectItem}', [WorkObjectController::class, 'updateItem'])->name('object-items.update');

    

});


require __DIR__.'/settings.php';
