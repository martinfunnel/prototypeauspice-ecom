<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Blade::if('canDo', function (string $permission) {
            $user = auth()->user();
            return $user && $user->canDo($permission);
        });

        Blade::if('cannotDo', function (string $permission) {
            $user = auth()->user();
            return !$user || !$user->canDo($permission);
        });

        Blade::if('isSuperAdmin', function () {
            $user = auth()->user();
            return $user && $user->isSuperAdmin();
        });
    }
}
