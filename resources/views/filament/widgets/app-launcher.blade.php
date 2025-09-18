<x-filament-widgets::widget>
    <x-filament::section>
        <ul class="space-y-4">
            @foreach($applications as $application)
                <li>
                    <x-filament::link :href="$this->getUrl($application)">
                        {{ $application->getLabel() }}
                    </x-filament::link>
                </li>
            @endforeach
        </ul>
    </x-filament::section>
</x-filament-widgets::widget>
