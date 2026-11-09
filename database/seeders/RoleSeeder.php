<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $guard = config('auth.defaults.guard');

        Role::findOrCreate(config('filament-shield.super_admin.name'), $guard);
        Role::findOrCreate(config('filament-shield.panel_user.name'), $guard);
    }
}
