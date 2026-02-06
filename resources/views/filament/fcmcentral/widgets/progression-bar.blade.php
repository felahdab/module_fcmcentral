<div class="space-y-1">
    <div class="flex justify-between text-xs">
        {{-- <span>{{ $getState()['completed'] }}/{{ $getState()['total'] }}</span> --}}
        <span class="font-medium">{{ $getState()['progress'] }}%</span> 
    </div>
    <div class="w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700">
        <div class="bg-primary-600 h-2 rounded-full transition-all duration-300" 
             style="width: {{ $getState()['progress'] }}%">
        </div>
    </div>
</div>

