<?php

namespace App\Filament\Pages\Auth;

use Filament\Pages\Auth\Login as BaseLogin;

class Login extends BaseLogin
{
    protected ?string $heading = null;

    protected ?string $subHeading = null;

    public function getView(): string
    {
        return 'filament.pages.auth.login';
    }

    public function getTitle(): string
    {
        return 'Login';
    }

    public static function getSlug(): string
    {
        return 'login';
    }
}
