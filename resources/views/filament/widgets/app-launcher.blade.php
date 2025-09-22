<x-filament-widgets::widget>
    <x-filament::section>
        <ul class="space-y-4">
            @foreach($applications as $application)
                @foreach($application->environments as $environment)
                    <li>
                        <x-filament::link :href="$this->getUrl($environment)">
                            {{ $application->name }}
                            <x-filament::badge>{{ $environment->name }}</x-filament::badge>
                        </x-filament::link>
                    </li>
                @endforeach
            @endforeach
        </ul>
    </x-filament::section>
</x-filament-widgets::widget>
