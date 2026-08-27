<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\ObjectItem;
use App\Models\ObjectSection;
use App\Models\Task;
use App\Models\WorkObject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class WorkObjectController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $workObject = DB::transaction(function () use ($validated) {
            $workObject = WorkObject::create([
                'name' => $validated['name'],
                'created_by' => auth()->id(),
            ]);

            $workObject->sections()->createMany([
                ['key' => 'documents', 'name' => 'Документы', 'position' => 0],
                ['key' => 'crew', 'name' => 'Задачи для выхода монтажной бригады', 'position' => 1],
                ['key' => 'materials', 'name' => 'Материалы', 'position' => 2],
            ]);

            return $workObject;
        });

        ActivityLog::record('object.created', "Создан объект «{$workObject->name}»");

        return response()->json($workObject, 201);
    }

    public function update(Request $request, WorkObject $workObject)
    {
        $oldName = $workObject->name;
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $workObject->update($validated);

        ActivityLog::record(
            'object.renamed',
            "Объект «{$oldName}» переименован",
            "Новое название: {$workObject->name}"
        );

        return response()->json($workObject->fresh());
    }

    public function destroy(WorkObject $workObject)
    {
        $name = $workObject->name;
        $workObject->delete();

        ActivityLog::record('object.deleted', "Удалён объект «{$name}»");

        return response()->noContent();
    }

    public function storeSection(Request $request, WorkObject $workObject)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $section = $workObject->sections()->create([
            'key' => 'custom_' . Str::uuid(),
            'name' => $validated['name'],
            'position' => ((int) $workObject->sections()->max('position')) + 1,
        ]);

        ActivityLog::record(
            'section.created',
            "Добавлен раздел «{$section->name}»",
            "Объект: {$workObject->name}"
        );

        return response()->json($section, 201);
    }

    public function updateSection(Request $request, ObjectSection $objectSection)
    {
        $oldName = $objectSection->name;
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $objectSection->update($validated);

        ActivityLog::record(
            'section.renamed',
            "Раздел «{$oldName}» переименован",
            "Новое название: {$objectSection->name}"
        );

        return response()->json($objectSection->fresh());
    }

    public function destroySection(ObjectSection $objectSection)
    {
        $sectionName = $objectSection->name;
        $objectName = $objectSection->workObject->name;

        DB::transaction(function () use ($objectSection) {
            ObjectItem::withTrashed()
                ->where('work_object_id', $objectSection->work_object_id)
                ->where('section', $objectSection->key)
                ->forceDelete();

            $objectSection->delete();
        });

        ActivityLog::record(
            'section.deleted',
            "Удалён раздел «{$sectionName}»",
            "Объект: {$objectName}"
        );

        return response()->noContent();
    }

    public function storeItem(Request $request, WorkObject $workObject)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'comment' => ['nullable', 'string'],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'section' => [
                'required',
                'string',
                Rule::exists('object_sections', 'key')
                    ->where('work_object_id', $workObject->id),
            ],
        ]);

        $item = $workObject->items()->create([
            ...$validated,
            'created_by' => auth()->id(),
        ]);

        $this->syncLinkedTask($item);

        ActivityLog::record(
            'object-item.created',
            "Добавлен пункт «{$item->title}»",
            "Объект: {$workObject->name}"
        );

        return response()->json($item, 201);
    }

    public function updateItem(Request $request, ObjectItem $objectItem)
    {
        $validated = $request->validate([
            'is_completed' => ['sometimes', 'boolean'],
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'comment' => ['nullable', 'string'],
            'priority' => ['sometimes', 'in:low,medium,high'],
            'timing' => ['sometimes', 'in:today,later,date'],
            'due_date' => ['nullable', 'date', 'required_if:timing,date'],
            'assigned_to' => ['sometimes', 'nullable', 'exists:users,id'],
        ]);

        abort_if($validated === [], 422, 'Нет данных для изменения.');

        $updates = [];
        if (array_key_exists('is_completed', $validated)) {
            $updates['is_completed'] = $validated['is_completed'];
            $updates['completed_at'] = $validated['is_completed'] ? now() : null;
        }
        if (array_key_exists('title', $validated)) {
            $updates['title'] = $validated['title'];
        }
        if (array_key_exists('comment', $validated)) {
            $updates['comment'] = $validated['comment'];
        }
        if (array_key_exists('assigned_to', $validated)) {
            $updates['assigned_to'] = $validated['assigned_to'];
        }

        DB::transaction(function () use ($objectItem, $updates, $validated) {
            $objectItem->update($updates);
            $this->syncLinkedTask($objectItem);

            $taskUpdates = array_intersect_key($validated, array_flip(['priority', 'timing', 'due_date']));
            if (array_key_exists('timing', $taskUpdates)) {
                $taskUpdates['due_date'] = match ($taskUpdates['timing']) {
                    'today' => today()->toDateString(),
                    'later' => null,
                    default => $validated['due_date'] ?? null,
                };
            }

            if ($taskUpdates && $objectItem->fresh()->linkedTask) {
                $objectItem->fresh()->linkedTask->update($taskUpdates);
            }
        });

        if (array_key_exists('is_completed', $validated)) {
            ActivityLog::record(
                $validated['is_completed'] ? 'object-item.completed' : 'object-item.reopened',
                ($validated['is_completed'] ? 'Выполнен пункт ' : 'Возвращён пункт ') . "«{$objectItem->title}»",
                "Объект: {$objectItem->workObject->name}"
            );
        } else {
            ActivityLog::record(
                'object-item.updated',
                "Изменён пункт «{$objectItem->title}»",
                "Объект: {$objectItem->workObject->name}"
            );
        }

        return response()->json($objectItem->fresh());
    }

    public function assignItem(Request $request, ObjectItem $objectItem)
    {
        $validated = $request->validate([
            'assigned_to' => ['nullable', 'exists:users,id'],
        ]);

        $objectItem->update($validated);

        $this->syncLinkedTask($objectItem);

        $assigneeName = $objectItem->assignee?->name ?? 'не назначен';
        ActivityLog::record(
            'object-item.assigned',
            "Изменён ответственный пункта «{$objectItem->title}»",
            "Ответственный: {$assigneeName}"
        );

        return response()->json($objectItem->fresh()->load('assignee'));
    }

    public function destroyItem(ObjectItem $objectItem)
    {
        $itemTitle = $objectItem->title;
        $objectName = $objectItem->workObject->name;
        $objectItem->linkedTask()->delete();
        $objectItem->delete();

        ActivityLog::record(
            'object-item.deleted',
            "Удалён пункт «{$itemTitle}»",
            "Объект: {$objectName}"
        );

        return response()->noContent();
    }

    public function restoreItem(int $objectItem)
    {
        $item = ObjectItem::onlyTrashed()->findOrFail($objectItem);

        $sectionExists = ObjectSection::where('work_object_id', $item->work_object_id)
            ->where('key', $item->section)
            ->exists();

        abort_unless($sectionExists, 422, 'Раздел этого пункта больше не существует.');

        $item->restore();
        $this->syncLinkedTask($item);

        ActivityLog::record(
            'object-item.restored',
            "Восстановлен пункт «{$item->title}»",
            "Объект: {$item->workObject->name}"
        );

        return response()->json($item->fresh());
    }

    private function syncLinkedTask(ObjectItem $item): void
    {
        if (! $item->assigned_to) {
            $item->linkedTask()->delete();

            return;
        }

        $task = $item->linkedTask;
        $attributes = [
            'title' => $item->title,
            'comment' => $item->comment,
            'assigned_to' => $item->assigned_to,
            'status' => $item->is_completed ? 'done' : 'new',
        ];

        if ($task) {
            $task->update($attributes);

            return;
        }

        Task::create([
            'object_item_id' => $item->id,
            ...$attributes,
            'created_by' => $item->created_by,
            'priority' => 'medium',
            'timing' => 'later',
            'due_date' => null,
        ]);
    }
}
