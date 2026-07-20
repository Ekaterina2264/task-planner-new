<?php

namespace App\Http\Controllers;

use App\Models\ObjectItem;
use App\Models\WorkObject;
use Illuminate\Http\Request;

class WorkObjectController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $workObject = WorkObject::create([
            'name' => $validated['name'],
            'created_by' => auth()->id(),
        ]);

        return response()->json($workObject, 201);
    }

    public function storeItem(Request $request, WorkObject $workObject)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'section' => ['required', 'in:documents,crew,materials'],
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
}
