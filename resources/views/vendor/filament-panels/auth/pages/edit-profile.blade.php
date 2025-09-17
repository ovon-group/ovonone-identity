@php
    $pageComponent = static::isSimple() ? 'filament-panels::page.simple' : 'filament-panels::page';
@endphp

<x-dynamic-component :component="$pageComponent">
    {{ $this->content }}

    <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                Passkey Authentication
            </h3>
            <p class="text-sm text-gray-500 mb-6">
                Passkeys provide a secure, passwordless way to sign in to your account.
                You can create multiple passkeys for different devices.
            </p>

            <livewire:passkeys />
        </div>
    </div>

</x-dynamic-component>
