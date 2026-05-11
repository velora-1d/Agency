<?php

namespace App\Support\Tenancy;

use App\Models\Workspace;

class WorkspaceContext
{
    protected static ?Workspace $workspace = null;

    public static function set(?Workspace $workspace): void
    {
        static::$workspace = $workspace;
    }

    public static function current(): ?Workspace
    {
        return static::$workspace;
    }

    public static function id(): ?string
    {
        return static::$workspace?->getKey();
    }

    public static function clear(): void
    {
        static::$workspace = null;
    }
}
