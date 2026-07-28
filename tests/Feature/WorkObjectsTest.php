<?php

use App\Models\ObjectItem;
use App\Models\ObjectSection;
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

    expect($object->sections)->toHaveCount(3);

    $this->actingAs($user)
        ->postJson(route('objects.items.store', $object), [
            'title' => 'Согласовать проект',
            'comment' => 'Передать заказчику финальную версию',
            'section' => 'documents',
        ])
        ->assertCreated();

    $item = $object->items()->firstOrFail();

    expect($item->title)->toBe('Согласовать проект');
    expect($item->comment)->toBe('Передать заказчику финальную версию');
    expect($item->section)->toBe('documents');
});

test('users can rename objects', function () {
    $user = User::factory()->create();
    $object = WorkObject::create([
        'name' => 'Старое название',
        'created_by' => $user->id,
    ]);

    $this->actingAs($user)
        ->patchJson(route('objects.update', $object), ['name' => 'Новое название'])
        ->assertOk()
        ->assertJsonPath('name', 'Новое название');

    expect($object->fresh()->name)->toBe('Новое название');
});

test('users can create rename and delete object sections', function () {
    $user = User::factory()->create();
    $object = WorkObject::create([
        'name' => 'Жилой комплекс',
        'created_by' => $user->id,
    ]);

    $response = $this->actingAs($user)
        ->postJson(route('objects.sections.store', $object), ['name' => 'Сметы'])
        ->assertCreated()
        ->assertJsonPath('name', 'Сметы');

    $section = ObjectSection::findOrFail($response->json('id'));

    $this->actingAs($user)
        ->patchJson(route('object-sections.update', $section), ['name' => 'Финансы'])
        ->assertOk()
        ->assertJsonPath('name', 'Финансы');

    $this->actingAs($user)
        ->deleteJson(route('object-sections.destroy', $section))
        ->assertNoContent();

    expect($section->fresh())->toBeNull();
});

test('deleting an object section also deletes its items', function () {
    $user = User::factory()->create();
    $object = WorkObject::create([
        'name' => 'Жилой комплекс',
        'created_by' => $user->id,
    ]);
    $section = $object->sections()->create([
        'key' => 'custom_section',
        'name' => 'Сметы',
        'position' => 0,
    ]);
    $item = ObjectItem::create([
        'work_object_id' => $object->id,
        'section' => $section->key,
        'title' => 'Проверить смету',
        'created_by' => $user->id,
    ]);

    $this->actingAs($user)
        ->deleteJson(route('object-sections.destroy', $section))
        ->assertNoContent();

    expect($item->fresh())->toBeNull();
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

test('users can delete and restore object items', function () {
    $user = User::factory()->create();
    $object = WorkObject::create([
        'name' => 'Жилой комплекс',
        'created_by' => $user->id,
    ]);
    $object->sections()->create([
        'key' => 'documents',
        'name' => 'Документы',
        'position' => 0,
    ]);
    $item = ObjectItem::create([
        'work_object_id' => $object->id,
        'section' => 'documents',
        'title' => 'Подписать договор',
        'created_by' => $user->id,
    ]);

    $this->actingAs($user)
        ->deleteJson(route('object-items.destroy', $item))
        ->assertNoContent();

    expect($item->fresh())->toBeNull();
    expect(ObjectItem::onlyTrashed()->find($item->id))->not->toBeNull();

    $this->actingAs($user)
        ->patchJson(route('object-items.restore', $item->id))
        ->assertOk()
        ->assertJsonPath('title', 'Подписать договор');

    expect($item->fresh())->not->toBeNull();
});
