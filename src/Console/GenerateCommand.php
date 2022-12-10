<?php

namespace ClaudioDekker\LaravelAuthBlade\Console;

use ClaudioDekker\LaravelAuth\Console\GenerateCommand as BaseGenerateCommand;

class GenerateCommand extends BaseGenerateCommand
{
    /**
     * Determines the path to this package.
     *
     * @return string
     */
    protected function determinePackagePath(): string
    {
        return dirname(__DIR__, 2);
    }

    protected function install(): void
    {
        parent::install();

        $this->exec('npm install');
        $this->installTailwindCss();
        $this->compileAssets();
    }

    /**
     * Installs the package's authentication routes.
     *
     * @return void
     */
    protected function installRoutes(): void
    {
        $this->copy('routes/web.stub', base_path('routes/web.php'));
    }

    /**
     * Installs the package's authentication tests.
     *
     * @return void
     */
    protected function installTests(): void
    {
        $this->generate('Tests.AuthenticationTest', base_path('tests/Feature/AuthenticationTest.php'));
    }

    /**
     * Installs the package's authentication views.
     *
     * @return void
     */
    protected function installViews(): void
    {
        $this->installLayouts();
        $this->installComponents();
        $this->installJs();

        $this->copy('views/auth/challenges/multi_factor.blade.stub', resource_path('views/auth/challenges/multi_factor.blade.php'));
        $this->copy('views/auth/challenges/recovery.blade.stub', resource_path('views/auth/challenges/recovery.blade.php'));
        $this->copy('views/auth/challenges/sudo_mode.blade.stub', resource_path('views/auth/challenges/sudo_mode.blade.php'));

        $this->copy('views/auth/settings/confirm_public_key.blade.stub', resource_path('views/auth/settings/confirm_public_key.blade.php'));
        $this->copy('views/auth/settings/confirm_recovery_codes.blade.stub', resource_path('views/auth/settings/confirm_recovery_codes.blade.php'));
        $this->copy('views/auth/settings/confirm_totp.blade.stub', resource_path('views/auth/settings/confirm_totp.blade.php'));
        $this->copy('views/auth/settings/credentials.blade.stub', resource_path('views/auth/settings/credentials.blade.php'));
        $this->copy('views/auth/settings/recovery_codes.blade.stub', resource_path('views/auth/settings/recovery_codes.blade.php'));

        $this->copy('views/auth/login.blade.stub', resource_path('views/auth/login.blade.php'));
        $this->copy('views/auth/recover-account.blade.stub', resource_path('views/auth/recover-account.blade.php'));
        $this->copy('views/auth/register.blade.stub', resource_path('views/auth/register.blade.php'));

        $this->copy('views/home.blade.stub', resource_path('views/home.blade.php'));
    }

    /**
     * Install the blade layouts used by the package's authentication views.
     *
     * @return void
     */
    protected function installLayouts(): void
    {
        $this->copy('views/layouts/guest.blade.stub', resource_path('views/layouts/guest.blade.php'));
    }

    /**
     * Install the blade components used by the package's authentication views.
     *
     * @return void
     */
    protected function installComponents(): void
    {
        // Vue Components (JS Forms)
        $this->copy('resources/js/components/icons/KeyIcon.vue', resource_path('js/components/icons/KeyIcon.vue'));
        $this->copy('resources/js/components/icons/PasswordIcon.vue', resource_path('js/components/icons/PasswordIcon.vue'));
        $this->copy('resources/js/components/ButtonComponent.vue', resource_path('js/components/ButtonComponent.vue'));
        $this->copy('resources/js/components/InputComponent.vue', resource_path('js/components/InputComponent.vue'));
        $this->copy('resources/js/components/InputErrorComponent.vue', resource_path('js/components/InputErrorComponent.vue'));
        $this->copy('resources/js/components/LoadingIndicatorComponent.vue', resource_path('js/components/LoadingIndicatorComponent.vue'));

        // Blade Components (Layouts)
        $this->copy('views/components/icons/users.blade.stub', resource_path('views/components/icons/users.blade.php'));
        $this->copy('views/components/anchor.blade.stub', resource_path('views/components/anchor.blade.php'));
    }

    /**
     * Installs the package's javascript files.
     *
     * @return void
     */
    protected function installJs(): void
    {
        $this->copy('vite.config.stub', base_path('vite.config.js'));

        $this->copy('resources/js/register.stub', resource_path('js/register.js'));
        $this->copy('resources/js/webauthn.stub', resource_path('js/webauthn.js'));

        $this->copy('resources/js/forms/RegisterForm.vue', resource_path('js/forms/RegisterForm.vue'));

        $this->exec('npm install -D @github/webauthn-json vue@next @vitejs/plugin-vue');
    }

    /**
     * Installs and configures Tailwind CSS.
     *
     * @return void
     */
    protected function installTailwindCss(): void
    {
        $this->copy('postcss.config.stub', base_path('postcss.config.js'));
        $this->copy('tailwind.config.stub', base_path('tailwind.config.js'));

        $this->copy('resources/css/app.stub', resource_path('css/app.css'));

        $this->exec('npm install -D tailwindcss postcss autoprefixer @tailwindcss/forms');
    }

    /**
     * Compiles the application's installed assets.
     *
     * @return void
     */
    protected function compileAssets(): void
    {
        $this->exec('npm run build');
    }
}
