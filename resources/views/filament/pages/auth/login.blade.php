<x-filament-panels::page.simple>
    @if (filament()->hasRegistration())
        <x-slot name="subheading">
            {{ __('filament-panels::pages/auth/login.actions.register.before') }}

            {{ $this->registerAction }}
        </x-slot>
    @endif

    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::AUTH_LOGIN_FORM_BEFORE, scopes: $this->getRenderHookScopes()) }}

    <x-filament-panels::form id="form" wire:submit="authenticate">
        {{ $this->form }}

        <x-filament-panels::form.actions
            :actions="$this->getCachedFormActions()"
            :full-width="$this->hasFullWidthFormActions()"
        />
    </x-filament-panels::form>

    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::AUTH_LOGIN_FORM_AFTER, scopes: $this->getRenderHookScopes()) }}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
        // Tunggu sebentar agar form benar-benar ter-render
            setTimeout(function() {
                const emailInput = document.querySelector('input[type="email"]');
                const passwordInput = document.querySelector('input[type="password"]');
        
                if (emailInput) {
                    emailInput.value = 'admin@gmail.com';
                }
        
                if (passwordInput) {
                passwordInput.value = 'password123';
                }
        
                // Optional: Auto-submit setelah 2 detik
            setTimeout(function() {
                const submitButton = document.querySelector('button[type="submit"]');
                if (submitButton) {
                    submitButton.click();
                }
            }, 2000);
        }, 500); // Delay 500ms untuk memastikan DOM ready
    });
    </script>
</x-filament-panels::page.simple>
