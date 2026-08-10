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
            'position'    => ((int) Task::where('assigned_to', $request->assigned_to)->max('position')) + 1,
            'comment'     => $request->comment,
        ]);

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
                'due_date' => $data['timing'] === 'date' ? $data['due_date'] : null,
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
        ]);

        $task->update($request->only('status', 'title', 'priority', 'timing', 'due_date', 'comment'));

        return response()->json(['success' => true]);
    }

    public function employees()
    {
        $employees = User::where('role', 'employee')
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
            ->orderBy('position')
            ->orderByRaw("CASE priority WHEN 'high' THEN 0 WHEN 'medium' THEN 1 ELSE 2 END")
            ->get();

        return response()->json([
            'user'  => $user->only('id', 'name', 'role'),
            'tasks' => $tasks,
        ]);
    }
}
