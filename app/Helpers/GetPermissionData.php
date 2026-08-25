<?php

namespace App\Helpers;

use App\Enums\Permissions\IsPermission;
use BackedEnum;

class GetPermissionData
{
    /**
     * Get the label and description for a permission by its name.
     *
     * @param  string  $permission  The permission name (e.g., 'view-users')
     * @return array{label: string, description: string}|null
     */
    public static function getPermissionData(string $permission): ?array
    {
        $permissionEnums = collect(glob(app_path('Enums/Permissions').'/*.php'))
            ->map(function ($file) {
                $className = pathinfo($file, PATHINFO_FILENAME);

                return "App\\Enums\\Permissions\\$className";
            })
            ->filter(fn ($class) => enum_exists($class) && is_subclass_of($class, IsPermission::class));

        foreach ($permissionEnums as $enumClass) {
            /** @var class-string<BackedEnum&IsPermission> $enumClass */
            $case = $enumClass::tryFrom($permission);

            if ($case instanceof IsPermission) {
                return [
                    'label' => $case->label(),
                    'description' => $case->description(),
                ];
            }
        }

        return null;
    }
}
