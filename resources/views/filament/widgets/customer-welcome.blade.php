<x-filament-widgets::widget>
    <x-filament::section class="bg-gradient-to-r from-purple-50 to-blue-50 dark:from-gray-800 dark:to-gray-900">
        <div class="space-y-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                    {{ $this->getGreeting() }}, {{ $this->getUserName() }}! 👋
                </h2>
                <p class="mt-1 text-gray-600 dark:text-gray-400">
                    Welcome to {{ $this->getCompanyName() }} Customer Portal • {{ $this->getCurrentDate() }}
                </p>
            </div>
            
            <div class="flex items-start gap-3 rounded-lg bg-white/50 dark:bg-gray-800/50 p-4">
                <x-heroicon-o-light-bulb class="h-5 w-5 text-yellow-500 mt-0.5" />
                <div>
                    <p class="font-medium text-gray-900 dark:text-white">
                        Tip of the day
                    </p>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        {{ $this->getRandomTip() }}
                    </p>
                </div>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>