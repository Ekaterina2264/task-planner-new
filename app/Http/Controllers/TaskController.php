<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TaskController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'title'       => ['required', 'string', 'max:255'],
            'assigned_to' => ['required', 'exists:users,id'],
            'priority'    => ['nullable', 'in:low,medium,high'],
            'timing'      => ['required', 'in:today,later,date'],
            'due_date'    => ['nullable', 'date', 'required_if:timing,date'],
        ]);

        $task = Task::create([
            'title'       => $request->title,
            'assigned_to' => $request->assigned_to,
            'created_by'  => auth()->id(),
            'priority'    => $request->priority ?? 'medium',
            'timing'      => $request->timing,
            'due_date'    => $request->timing === 'today'
                ? today()->toDateString()
                : ($request->timing === 'date' ? $request->due_date : null),
            'status'      => 'new',
            'position'    => ((int) Task::where('assigned_to', $request->assigned_to)->max('position')) + 1,
            'comment'     => $request->comment,
        ]);

        ActivityLog::record(
            'task.created',
            "Создана задача «{$task->title}»",
            'Ответственный: ' . $task->assignee()->value('name')
        );

        return response()->json(['success' => true]);
    }

    public function move(Request $request, Task $task)
    {
        $data = $request->validate([
            'timing' => ['required', 'in:today,later,date'],
            'due_date' => ['nullable', 'date', 'required_if:timing,date'],
            'ordered_task_ids' => ['required', 'array', 'min:1'],
            'ordered_task_ids.*' => ['integer', 'distinct'],
        ]);

        $ids = collect($data['ordered_task_ids'])->map(fn ($id) => (int) $id);
        abort_unless($ids->contains($task->id), 422);
        abort_unless(Task::where('assigned_to', $task->assigned_to)->whereIn('id', $ids)->count() === $ids->count(), 422);

        DB::transaction(function () use ($task, $data, $ids) {
            $task->update([
                'timing' => $data['timing'],
                'due_date' => $data['timing'] === 'date'
                    ? $data['due_date']
                    : ($data['timing'] === 'today' ? today()->toDateString() : null),
            ]);

            foreach ($ids as $index => $id) {
                Task::whereKey($id)->update(['position' => $index + 1]);
            }
        });

        return response()->json(['success' => true]);
    }

    public function update(Request $request, Task $task)
    {
        $request->validate([
            'status'   => ['sometimes', 'in:new,done'],
            'title'    => ['sometimes', 'string', 'max:255'],
            'priority' => ['sometimes', 'in:low,medium,high'],
            'timing'   => ['sometimes', 'in:today,later,date'],
            'due_date' => ['nullable', 'date'],
            'comment'  => ['nullable', 'string'],
            'assigned_to' => ['sometimes', 'exists:users,id'],
        ]);

        $originalStatus = $task->status;
        $originalTitle = $task->title;
        $originalAssignedTo = $task->assigned_to;
        $updates = $request->only('status', 'title', 'priority', 'timing', 'due_date', 'comment', 'assigned_to');

        if ($request->has('assigned_to') && (int) $request->assigned_to !== (int) $task->assigned_to) {
            $updates['position'] = ((int) Task::where('assigned_to', $request->assigned_to)->max('position')) + 1;
        }

        if ($request->has('timing')) {
            if ($request->timing === 'today') {
                $updates['due_date'] = today()->toDateString();
            } elseif ($request->timing === 'later') {
                $updates['due_date'] = null;
            }
        }

        DB::transaction(function () use ($request, $task, $updates) {
            $task->update($updates);

            if (! $task->object_item_id) {
                return;
            }

            $itemUpdates = [];

            if ($request->has('status')) {
                $completed = $request->status === 'done';
                $itemUpdates['is_completed'] = $completed;
                $itemUpdates['completed_at'] = $completed ? now() : null;
            }

            if ($request->has('title')) {
                $itemUpdates['title'] = $request->title;
            }

            if ($request->has('comment')) {
                $itemUpdates['comment'] = $request->comment;
            }

            if ($request->has('assigned_to')) {
                $itemUpdates['assigned_to'] = $request->assigned_to;
            }

            if ($itemUpdates) {
                $task->objectItem()->update($itemUpdates);
            }
        });

        if ($request->has('status') && $request->status !== $originalStatus) {
            ActivityLog::record(
                $request->status === 'done' ? 'task.completed' : 'task.reopened',
                ($request->status === 'done' ? 'Выполнена задача ' : 'Возвращена задача ') . "«{$task->fresh()->title}»"
            );
        } elseif ($request->has('title') && $request->title !== $originalTitle) {
            ActivityLog::record(
                'task.renamed',
                "Задача «{$originalTitle}» переименована",
                "Новое название: {$request->title}"
            );
        } elseif ($request->has('assigned_to') && (int) $request->assigned_to !== (int) $originalAssignedTo) {
            ActivityLog::record(
                'task.reassigned',
                "Переназначена задача «{$task->fresh()->title}»",
                'Новый ответственный: ' . $task->fresh()->assignee()->value('name')
            );
        } elseif ($task->wasChanged()) {
            ActivityLog::record('task.updated', "Изменена задача «{$task->fresh()->title}»");
        }

        return response()->json(['success' => true]);
    }

    public function destroy(Task $task)
    {
        $user = auth()->user();
        abort_unless(
            $user->isDirector() || $task->assigned_to === $user->id || $task->created_by === $user->id,
            403
        );

        $title = $task->title;
        $task->delete();

        ActivityLog::record('task.deleted', "Удалена задача «{$title}»");

        return response()->json(['success' => true]);
    }

    public function employees()
    {
        $employees = User::query()
            ->withCount(['tasks as open_tasks_count' => function ($q) {
                $q->where('status', 'new');
            }])
            ->orderByRaw('CASE WHEN id = ? THEN 0 ELSE 1 END', [auth()->id()])
            ->orderBy('name')
            ->get();

        return response()->json($employees);
    }

    public function employeeTasks(User $user)
    {
        $tasks = Task::where('assigned_to', $user->id)
            ->with('objectItem.workObject')
            ->orderBy('position')
            ->orderByRaw("CASE priority WHEN 'high' THEN 0 WHEN 'medium' THEN 1 ELSE 2 END")
            ->get();

        return response()->json([
            'user'  => $user->only('id', 'name', 'role'),
            'tasks' => $tasks,
        ]);
    }
}
