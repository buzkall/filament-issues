<x-filament-widgets::widget>
    <x-filament::section>
        <div class="flex items-center gap-x-3">
            <div class="flex-1">
                <div class="flex justify-between">
                    <h2 class="font-semibold text-gray-950 dark:text-white">
                        {{ __('Money this month') }} ({{ $this->data['monthFormatted'] }})
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ $this->data['hours'] }}
                    </p>
                </div>

                <div class="flex gap-x-3 items-center">
                    <x-filament::icon icon="heroicon-m-banknotes"
                                      class="h-5 w-5 text-gray-500 dark:text-gray-400"
                    />
                    <p class="text-3xl font-semibold tracking-tight text-gray-950 dark:text-white">
                        {{ $this->data['money'] }}
                    </p>
                </div>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
