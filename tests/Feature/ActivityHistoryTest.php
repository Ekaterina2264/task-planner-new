<?php

use App\Models\ActivityLog;
use App\Models\User;

test('authenticated users can open activity history', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('dashboard', ['view' => 'history']))
        ->assertOk()
        ->assertSee('История');
});

test('creating an object is recorded in activity history', function () {
    $user = User::factory()->create(['name' => 'Иван Директор']);

    $this->actingAs($user)
        ->postJson(route('objects.store'), ['name' => 'БЦ Атлас'])
        ->assertCreated();

    $activity = ActivityLog::firstOrFail();

    expect($activity->user_id)->toBe($user->id);
    expect($activity->event)->toBe('object.created');
    expect($activity->description)->toBe('Создан объект «БЦ Атлас»');
});
