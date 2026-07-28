<?php

namespace App\Http\Controllers;

use App\Models\ObjectItem;
use App\Models\ObjectSection;
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

        return response()->json($workObject, 201);
    }

    public function update(Request $request, WorkObject $workObject)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $workObject->update($validated);

        return response()->json($workObject->fresh());
    }

    public function destroy(WorkObject $workObject)
    {
        $workObject->delete();

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

        return response()->json($section, 201);
    }

    public function updateSection(Request $request, ObjectSection $objectSection)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $objectSection->update($validated);

        return response()->json($objectSection->fresh());
    }

    public function destroySection(ObjectSection $objectSection)
    {
        DB::transaction(function () use ($objectSection) {
            ObjectItem::withTrashed()
                ->where('work_object_id', $objectSection->work_object_id)
                ->where('section', $objectSection->key)
                ->forceDelete();

            $objectSection->delete();
        });

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

        return response()->json($item, 201);
    }

    public function updateItem(Request $request, ObjectItem $objectItem)
    {
        $validated = $request->validate([
            'is_completed' => ['required', 'boolean'],
        ]);

        $objectItem->update([
            'is_completed' => $validated['is_completed'],
            'completed_at' => $validated['is_completed'] ? now() : null,
        ]);

        return response()->json($objectItem->fresh());
    }

    public function assignItem(Request $request, ObjectItem $objectItem)
    {
        $validated = $request->validate([
            'assigned_to' => ['nullable', 'exists:users,id'],
        ]);

        $objectItem->update($validated);

        return response()->json($objectItem->fresh()->load('assignee'));
    }

    public function destroyItem(ObjectItem $objectItem)
    {
        $objectItem->delete();

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

        return response()->json($item->fresh());
    }
}
