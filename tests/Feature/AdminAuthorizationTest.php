<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

it('redirects guests away from admin routes', function () {
    $this->get(route('admin.dashboard'))
        ->assertRedirect(route('login'));
});

it('blocks authenticated users without admin access', function () {
    $user = User::factory()->create(['role' => User::ROLE_CUSTOMER]);

    $this->actingAs($user)
        ->get(route('admin.dashboard'))
        ->assertForbidden();
});

it('allows staff role users into the admin dashboard', function () {
    $staff = User::factory()->create([
        'role' => User::ROLE_STAFF,
        'is_admin' => false,
    ]);

    $this->actingAs($staff)
        ->get(route('admin.dashboard'))
        ->assertOk();
});

it('allows admins into the admin dashboard', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('Catalog admin foundation');
});

it('logs in only admin users through the admin login form', function () {
    $admin = User::factory()->admin()->create([
        'email' => 'owner@azraq.test',
    ]);
    $customer = User::factory()->create([
        'email' => 'customer@azraq.test',
    ]);

    $this->post(route('admin.login.store'), [
        'email' => $customer->email,
        'password' => 'password',
    ])->assertSessionHasErrors('email');

    $this->post(route('admin.login.store'), [
        'email' => $admin->email,
        'password' => 'password',
    ])->assertRedirect(route('admin.dashboard'));
});

it('lets admins manage users from the admin panel', function () {
    signInAdmin($this);

    $this->get(route('admin.users.index'))
        ->assertOk()
        ->assertSee('Users');

    $this->post(route('admin.users.store'), [
        'name' => 'Operations Admin',
        'email' => 'ops@azraq.test',
        'password' => 'secret-password',
        'password_confirmation' => 'secret-password',
        'role' => User::ROLE_MANAGER,
    ])->assertRedirect(route('admin.users.index'));

    $user = User::where('email', 'ops@azraq.test')->firstOrFail();

    expect($user->role)->toBe(User::ROLE_MANAGER)
        ->and($user->is_admin)->toBeTrue()
        ->and($user->isAdmin())->toBeTrue()
        ->and(Hash::check('secret-password', $user->password))->toBeTrue();

    $this->put(route('admin.users.update', $user), [
        'name' => 'Operations User',
        'email' => 'ops-user@azraq.test',
        'password' => '',
        'password_confirmation' => '',
        'role' => User::ROLE_CUSTOMER,
    ])->assertRedirect(route('admin.users.index'));

    $user->refresh();

    expect($user->name)->toBe('Operations User')
        ->and($user->email)->toBe('ops-user@azraq.test')
        ->and($user->role)->toBe(User::ROLE_CUSTOMER)
        ->and($user->is_admin)->toBeFalse();

    $this->delete(route('admin.users.destroy', $user))
        ->assertRedirect(route('admin.users.index'));

    expect(User::whereKey($user->id)->exists())->toBeFalse();
});

it('protects the current admin account from unsafe user changes', function () {
    $admin = signInAdmin($this);

    $this->put(route('admin.users.update', $admin), [
        'name' => $admin->name,
        'email' => $admin->email,
        'password' => '',
        'password_confirmation' => '',
        'role' => User::ROLE_CUSTOMER,
    ])->assertSessionHasErrors('role');

    $this->delete(route('admin.users.destroy', $admin))
        ->assertRedirect();

    expect(User::whereKey($admin->id)->exists())->toBeTrue()
        ->and($admin->refresh()->is_admin)->toBeTrue();
});
