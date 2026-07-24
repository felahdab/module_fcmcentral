@if($this->marin->estGele())
    <div class="mb-6 rounded-lg border border-orange-300 bg-orange-50 dark:bg-orange-900/20 dark:border-orange-700 px-4 py-3 flex items-center gap-3">
        <x-filament::icon icon="heroicon-o-exclamation-triangle" class="w-5 h-5 text-orange-600 dark:text-orange-400 flex-shrink-0" />
        <span class="text-sm font-medium text-orange-800 dark:text-orange-200">
            Ce marin est actuellement en gel.
        </span>
    </div>
@endif