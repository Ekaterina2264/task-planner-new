<?php

namespace App\Http\Controllers;

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

        Task::create([
            'title'       => $request->title,
            'assigned_to' => $request->assigned_to,
            'created_by'  => auth()->id(),
            'priority'    => $request->priority ?? 'medium',
            'timing'      => $request->timing,
            'due_date'    => $request->timing === 'date' ? $request->due_date : null,
            'status'      => 'new',
            'comment'     => $request->comment,
        ]);

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
        ]);

        DB::transaction(function () use ($request, $task) {
            $task->update($request->only('status', 'title', 'priority', 'timing', 'due_date', 'comment'));

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

            if ($itemUpdates) {
                $task->objectItem()->update($itemUpdates);
            }
        });

        return response()->json(['success' => true]);
    }

    public function employees()
    {
        $employees = User::whereKeyNot(auth()->id())
            ->withCount(['tasks as open_tasks_count' => function ($q) {
                $q->where('status', 'new');
            }])
            ->orderBy('name')
            ->get();

        return response()->json($employees);
    }

    public function employeeTasks(User $user)
    {
        $tasks = Task::where('assigned_to', $user->id)
            ->with('objectItem.workObject')
            ->orderByRaw("CASE priority WHEN 'high' THEN 0 WHEN 'medium' THEN 1 ELSE 2 END")
            ->get();

        return response()->json([
            'user'  => $user->only('id', 'name', 'role'),
            'tasks' => $tasks,
        ]);
    }
}
