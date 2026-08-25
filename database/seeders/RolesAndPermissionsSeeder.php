<?php

namespace Database\Seeders;

use App\Enums\Roles;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $guardName = 'web';

        $existingPermissions = Permission::where('guard_name', $guardName)->get()->pluck('name');
        $allPermissions = collect(glob(app_path('Enums/Permissions').'/*.php'))
            ->map(function ($file) {
                $className = pathinfo($file, PATHINFO_FILENAME);
                $class = "App\Enums\Permissions\\$className";

                return $class;
            })
            ->filter(fn ($class) => enum_exists($class))
            ->map(fn ($class) => array_column($class::cases(), 'value'))
            ->flatten();

        $newPermissions = $allPermissions->diff($existingPermissions);
        $removedPermissions = $existingPermissions->diff($allPermissions);

        Permission::whereIn('name', $removedPermissions)->where('guard_name', $guardName)->delete();
        Permission::insert($newPermissions->map(fn ($permission) => [
            'name' => $permission,
            'guard_name' => $guardName,
            'created_at' => now()->toDateTimeString(),
            'updated_at' => now()->toDateTimeString(),
        ])->toArray());

        $existingRoles = Role::where('guard_name', $guardName)->get()->pluck('name');
        $allRoles = collect(array_column(Roles::cases(), 'value'));

        $newRoles = $allRoles->diff($existingRoles);
        $removedRoles = $existingRoles->diff($allRoles);

        Role::whereIn('name', $removedRoles)->where('guard_name', $guardName)->delete();
        Role::insert($newRoles->map(fn ($role) => [
            'name' => $role,
            'guard_name' => $guardName,
            'created_at' => now()->toDateTimeString(),
            'updated_at' => now()->toDateTimeString(),
        ])->toArray());

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
