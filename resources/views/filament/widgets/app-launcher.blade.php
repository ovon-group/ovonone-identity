<x-filament-widgets::widget>
    <div class="grid grid-cols-2 gap-6 w-full">
        @foreach($applications as $appData)
            @php
                $application = $appData['application'];
                $canAccess = $appData['canAccess'];
            @endphp
            
            @if($canAccess)
                <x-filament::card class="group hover:shadow-md transition-shadow duration-200 cursor-pointer">
                    <x-filament::link :href="$this->getUrl($application)" class="block h-full no-underline">
                        <div class="p-6">
                            <div class="flex items-center space-x-4 mb-4">
                                <div class="flex-shrink-0">
                                    <x-filament::icon 
                                        :icon="$application->getIcon()" 
                                        :class="'w-8 h-8 text-' . $application->getColor() . '-600'"
                                    />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white group-hover:text-{{ $application->getColor() }}-600 transition-colors duration-200">
                                        {{ $application->getLabel() }}
                                    </h3>
                                </div>
                            </div>
                            
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                                {{ $application->getDescription() }}
                            </p>
                            
                            <div class="inline-flex items-center text-sm font-medium text-{{ $application->getColor() }}-600 group-hover:text-{{ $application->getColor() }}-700 dark:text-{{ $application->getColor() }}-400 dark:group-hover:text-{{ $application->getColor() }}-300 transition-colors duration-200">
                                Access Application
                                <x-heroicon-o-arrow-top-right-on-square class="w-4 h-4 ml-1" />
                            </div>
                        </div>
                    </x-filament::link>
                </x-filament::card>
            @else
                <x-filament::card class="group transition-shadow duration-200 opacity-50 cursor-not-allowed">
                    <div class="block h-full">
                        <div class="p-6">
                            <div class="flex items-center space-x-4 mb-4">
                                <div class="flex-shrink-0">
                                    <x-filament::icon 
                                        :icon="$application->getIcon()" 
                                        class="w-8 h-8 text-gray-400"
                                    />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-lg font-semibold text-gray-500 dark:text-gray-500">
                                        {{ $application->getLabel() }}
                                    </h3>
                                </div>
                            </div>
                            
                            <p class="text-sm text-gray-400 dark:text-gray-500 mb-4">
                                {{ $application->getDescription() }}
                            </p>
                            
                            <div class="inline-flex items-center text-sm font-medium text-gray-400 dark:text-gray-500">
                                No Access
                                <x-heroicon-o-lock-closed class="w-4 h-4 ml-1" />
                            </div>
                        </div>
                    </div>
                </x-filament::card>
            @endif
        @endforeach
    </div>
</x-filament-widgets::widget>
