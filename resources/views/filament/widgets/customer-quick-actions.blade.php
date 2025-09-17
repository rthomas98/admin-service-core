<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            ⚡ Quick Actions
        </x-slot>
        
        <x-slot name="description">
            Common tasks and shortcuts
        </x-slot>
        
        <div class="grid grid-cols-1 gap-3">
            @foreach($this->getActions() as $action)
                <a
                    href="{{ $action['url'] }}"
                    class="group relative flex items-start gap-3 rounded-lg border border-gray-200 dark:border-gray-700 p-3 transition hover:bg-gray-50 dark:hover:bg-gray-800/50"
                >
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-{{ $action['color'] }}-50 dark:bg-{{ $action['color'] }}-900/20">
                        <x-dynamic-component 
                            :component="'heroicon-o-' . str_replace('heroicon-o-', '', $action['icon'])"
                            class="h-5 w-5 text-{{ $action['color'] }}-600 dark:text-{{ $action['color'] }}-400"
                        />
                    </div>
                    
                    <div class="flex-1">
                        <div class="font-medium text-gray-900 dark:text-white group-hover:text-{{ $action['color'] }}-600 dark:group-hover:text-{{ $action['color'] }}-400">
                            {{ $action['label'] }}
                        </div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">
                            {{ $action['description'] }}
                        </div>
                    </div>
                    
                    <x-heroicon-m-arrow-right class="h-5 w-5 text-gray-400 group-hover:text-{{ $action['color'] }}-600 dark:group-hover:text-{{ $action['color'] }}-400 transition" />
                </a>
            @endforeach
        </div>
    </x-filament::section>
</x-filament-widgets::widget>