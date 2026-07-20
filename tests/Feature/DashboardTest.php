<?php

use App\Models\User;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertOk();
});

test('employees see personal tasks and team navigation', function () {
    $employee = User::factory()->create(['role' => 'employee']);

    $this->actingAs($employee)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Мои задачи')
        ->assertSee('Команда');
});

test('employees can open the team view', function () {
    $employee = User::factory()->create(['role' => 'employee']);

    $this->actingAs($employee)
        ->get(route('dashboard', ['view' => 'team']))
        ->assertOk()
        ->assertSee('Список сотрудников и их задачи');
});
