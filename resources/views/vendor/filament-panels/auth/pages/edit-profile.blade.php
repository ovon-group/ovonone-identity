@php
    $pageComponent = static::isSimple() ? 'filament-panels::page.simple' : 'filament-panels::page';
@endphp

<div>
    @if(session()->has('referer'))
        <x-filament::button
            tag="a"
            :icon="\Filament\Support\Icons\Heroicon::ArrowLeft"
            :href="session('referer')"
        >Back to app</x-filament::button>
    @endif

    <x-dynamic-component :component="$pageComponent">
        {{ $this->content }}
    </x-dynamic-component>

    <hr class="border-gray-200 bt-1 my-10"/>

    <div>
        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
            Passkey Authentication
        </h3>
        <p class="text-sm text-gray-500 mb-6">
            Passkeys provide a secure, passwordless way to sign in to your account.
            You can create multiple passkeys for different devices.
        </p>

        <livewire:passkeys/>
    </div>
</div>
