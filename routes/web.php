<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\WorkObjectController;
use App\Models\ActivityLog;
use App\Models\ObjectItem;
use App\Models\User;
use App\Models\WorkObject;

Route::redirect('/', '/dashboard');

Route::middleware(['auth'])->group(function () {

    Route::get('/dashboard', function () {
        $user = auth()->user();

        if (request('view') === 'team') {
            $objects = WorkObject::withCount([
                'items as active_items_count' => fn ($query) => $query->where('is_completed', false),
                'items',
            ])->orderBy('name')->get();

            return view('director.dashboard', compact('objects'));
        }

        if (request('view') === 'objects') {
            $selectedObjectId = request()->integer('object');
            $objectsQuery = WorkObject::with(['items.assignee', 'deletedItems', 'sections'])->orderBy('name');

            if ($selectedObjectId) {
                $objectsQuery->whereKey($selectedObjectId);
            }

            $objects = $objectsQuery->get();
            $deletedItems = ObjectItem::onlyTrashed()
                ->with(['workObject.sections'])
                ->orderByDesc('deleted_at')
                ->get();
            $employees = User::orderBy('name')->get();

            return view('objects.index', compact('objects', 'deletedItems', 'employees', 'selectedObjectId'));
        }

        if (request('view') === 'history') {
            $activities = ActivityLog::with('user')
                ->latest()
                ->limit(200)
                ->get();

            return view('history.index', compact('activities'));
        }

        $allTasks = \App\Models\Task::where('assigned_to', $user->id)
            ->with('objectItem.workObject')
            ->orderBy('position')
            ->orderByRaw("CASE priority WHEN 'high' THEN 0 WHEN 'medium' THEN 1 ELSE 2 END")
            ->get();
        $tasks = $allTasks->where('status', 'new');

        $today     = now()->startOfDay();
        $tomorrow  = now()->addDay()->startOfDay();
        $effectiveDueDate = fn($task) => $task->due_date?->copy()->startOfDay()
            ?? ($task->timing === 'today' ? $task->created_at->copy()->startOfDay() : null);

        $allBySection = [
            'overdue' => $allTasks->filter(fn($t) => in_array($t->timing, ['today', 'date'], true)
                && ($date = $effectiveDueDate($t))
                && $date->lt($today)),
            'today' => $allTasks->filter(fn($t) => in_array($t->timing, ['today', 'date'], true)
                && ($date = $effectiveDueDate($t))
                && $date->eq($today)),
            'tomorrow' => $allTasks->filter(fn($t) => $t->timing === 'date' && $t->due_date && $t->due_date->startOfDay()->eq($tomorrow)),
            'later' => $allTasks->filter(fn($t) => $t->timing === 'later' || ($t->timing === 'date' && $t->due_date && $t->due_date->startOfDay()->gt($tomorrow))),
        ];
        $overdue = $allBySection['overdue']->where('status', 'new');
        $todayT = $allBySection['today']->where('status', 'new');
        $tomorrowT = $allBySection['tomorrow']->where('status', 'new');
        $laterT = $allBySection['later']->where('status', 'new');
        $sectionProgress = collect($allBySection)->map(function ($sectionTasks) {
            $visibleTasks = $sectionTasks->filter(fn($task) => $task->status !== 'done'
                || $task->updated_at->isToday());

            return [
                'done' => $visibleTasks->where('status', 'done')->count(),
                'total' => $visibleTasks->count(),
            ];
        });

        $tasksView = $user->isDirector() ? 'director.tasks' : 'employee.dashboard';
        $employees = User::orderBy('name')->get();

        return view($tasksView, compact('overdue', 'todayT', 'tomorrowT', 'laterT', 'sectionProgress', 'employees'));
    })->name('dashboard');

    // Задачи
    Route::post('/tasks', [TaskController::class, 'store'])->name('tasks.store');
    Route::patch('/tasks/{task}', [TaskController::class, 'update'])->name('tasks.update');
    Route::patch('/tasks/{task}/move', [TaskController::class, 'move'])->name('tasks.move');

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
    Route::patch('/object-items/{objectItem}/assignee', [WorkObjectController::class, 'assignItem'])->name('object-items.assignee');
    Route::delete('/object-items/{objectItem}', [WorkObjectController::class, 'destroyItem'])->name('object-items.destroy');
    Route::patch('/object-items/{objectItem}/restore', [WorkObjectController::class, 'restoreItem'])->name('object-items.restore');

    

});


require __DIR__.'/settings.php';
