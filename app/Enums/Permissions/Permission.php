<?php

namespace App\Enums\Permissions;

trait Permission
{
    public static function names(): array
    {
        return array_column(self::cases(), 'name');
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function labels(): array
    {
        return array_map(fn (self $permission) => $permission->label(), self::cases());
    }

    public static function descriptions(): array
    {
        return array_map(fn (self $permission) => $permission->description(), self::cases());
    }

    public static function toArray(): array
    {
        return array_map(fn (self $permission) => [
            'name' => $permission->name,
            'value' => $permission->value,
            'label' => $permission->label(),
            'description' => $permission->description(),
        ], self::cases());
    }
}
