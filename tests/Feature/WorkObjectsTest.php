<?php

use App\Models\ObjectItem;
use App\Models\User;
use App\Models\WorkObject;

test('authenticated users can open objects', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('dashboard', ['view' => 'objects']))
        ->assertOk()
        ->assertSee('Объекты')
        ->assertSee('Новый объект');
});

test('users can create an object and its items', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->postJson(route('objects.store'), ['name' => 'Жилой комплекс'])
        ->assertCreated();

    $object = WorkObject::firstOrFail();

    $this->actingAs($user)
        ->postJson(route('objects.items.store', $object), [
            'title' => 'Согласовать проект',
            'section' => 'documents',
        ])
        ->assertCreated();

    $item = $object->items()->firstOrFail();

    expect($item->title)->toBe('Согласовать проект');
    expect($item->section)->toBe('documents');
});

test('completed object items keep their completion date', function () {
    $user = User::factory()->create();
    $object = WorkObject::create([
        'name' => 'Жилой комплекс',
        'created_by' => $user->id,
    ]);
    $item = ObjectItem::create([
        'work_object_id' => $object->id,
        'section' => 'documents',
        'title' => 'Согласовать проект',
        'created_by' => $user->id,
    ]);

    $this->actingAs($user)
        ->patchJson(route('object-items.update', $item), ['is_completed' => true])
        ->assertOk()
        ->assertJsonPath('is_completed', true);

    expect($item->fresh()->completed_at)->not->toBeNull();
});
