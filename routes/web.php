<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\WorkObjectController;
use App\Models\ObjectItem;
use App\Models\WorkObject;

Route::redirect('/', '/dashboard');

Route::middleware(['auth'])->group(function () {

    Route::get('/dashboard', function () {
        $user = auth()->user();

        if (request('view') === 'team') {
            return view('director.dashboard');
        }

        if (request('view') === 'objects') {
            $objects = WorkObject::with(['items', 'deletedItems', 'sections'])->orderBy('name')->get();
            $deletedItems = ObjectItem::onlyTrashed()
                ->with(['workObject.sections'])
                ->orderByDesc('deleted_at')
                ->get();

            return view('objects.index', compact('objects', 'deletedItems'));
        }

        $allTasks = \App\Models\Task::where('assigned_to', $user->id)
            ->orderByRaw("CASE priority WHEN 'high' THEN 0 WHEN 'medium' THEN 1 ELSE 2 END")
            ->get();
        $tasks = $allTasks->where('status', 'new');

        $today     = now()->startOfDay();
        $tomorrow  = now()->addDay()->startOfDay();
        $weekEnd   = now()->addDays(7)->startOfDay();

        $allBySection = [
            'overdue' => $allTasks->filter(fn($t) => $t->timing === 'date' && $t->due_date && $t->due_date->startOfDay()->lt($today)),
            'today' => $allTasks->filter(fn($t) => $t->timing === 'today' || ($t->timing === 'date' && $t->due_date && $t->due_date->startOfDay()->eq($today))),
            'tomorrow' => $allTasks->filter(fn($t) => $t->timing === 'date' && $t->due_date && $t->due_date->startOfDay()->eq($tomorrow)),
            'week' => $allTasks->filter(fn($t) => $t->timing === 'date' && $t->due_date && $t->due_date->startOfDay()->gt($tomorrow) && $t->due_date->startOfDay()->lt($weekEnd)),
            'later' => $allTasks->filter(fn($t) => $t->timing === 'later' || ($t->timing === 'date' && $t->due_date && $t->due_date->startOfDay()->gte($weekEnd))),
        ];
        $overdue = $allBySection['overdue']->where('status', 'new');
        $todayT = $allBySection['today']->where('status', 'new');
        $tomorrowT = $allBySection['tomorrow']->where('status', 'new');
        $weekT = $allBySection['week']->where('status', 'new');
        $laterT = $allBySection['later']->where('status', 'new');
        $sectionProgress = collect($allBySection)->map(fn($sectionTasks) => [
            'done' => $sectionTasks->where('status', 'done')->count(),
            'total' => $sectionTasks->count(),
        ]);

        $tasksView = $user->isDirector() ? 'director.tasks' : 'employee.dashboard';

        return view($tasksView, compact('overdue', 'todayT', 'tomorrowT', 'weekT', 'laterT', 'sectionProgress'));
    })->name('dashboard');

    // Задачи
    Route::post('/tasks', [TaskController::class, 'store'])->name('tasks.store');
    Route::patch('/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');

    // Директор — API
    Route::get('/api/employees', [TaskController::class, 'employees'])->name('api.employees');
    Route::get('/api/employees/{user}/tasks', [TaskController::class, 'employeeTasks'])->name('api.employee.tasks');

    // Объекты
    Route::post('/objects', [WorkObjectController::class, 'store'])->name('objects.store');
    Route::patch('/objects/{workObject}', [WorkObjectController::class, 'update'])->name('objects.update');
    Route::delete('/objects/{workObject}', [WorkObjectController::class, 'destroy'])->name('objects.destroy');
    Route::post('/objects/{workObject}/sections', [WorkObjectController::class, 'storeSection'])->name('objects.sections.store');
    Route::patch('/object-sections/{objectSection}', [WorkObjectController::class, 'updateSection'])->name('object-sections.update');
    Route::delete('/object-sections/{objectSection}', [WorkObjectController::class, 'destroySection'])->name('object-sections.destroy');
    Route::post('/objects/{workObject}/items', [WorkObjectController::class, 'storeItem'])->name('objects.items.store');
    Route::patch('/object-items/{objectItem}', [WorkObjectController::class, 'updateItem'])->name('object-items.update');
    Route::delete('/object-items/{objectItem}', [WorkObjectController::class, 'destroyItem'])->name('object-items.destroy');
    Route::patch('/object-items/{objectItem}/restore', [WorkObjectController::class, 'restoreItem'])->name('object-items.restore');

    

});


require __DIR__.'/settings.php';
