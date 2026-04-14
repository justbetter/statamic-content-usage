<div class="p-4">
    <h3 class="font-bold text-base mb-2">{{ __('statamic-content-usage::widgets.asset-usage.heading') }}</h3>
    <p class="text-xs mb-4">{{ __('statamic-content-usage::widgets.asset-usage.description') }}</p>

    <form method="GET" action="{{ $exportUrl }}" class="space-y-4" id="asset-usage-form">
        <div>
            <label class="block text-xs font-bold mb-2">
                {{ __('statamic-content-usage::widgets.asset-usage.containers_label') }}
            </label>
            @if($containers->isEmpty())
                <p class="text-xs text-gray-500">{{ __('statamic-content-usage::widgets.asset-usage.no_containers') }}</p>
            @else
                <div class="space-y-2 max-h-48 overflow-y-auto">
                    <p class="block text-xs mb-2">{{ __('statamic-content-usage::widgets.asset-usage.containers_instructions') }}</p>

                    @foreach ($containers as $container)
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input
                                type="checkbox"
                                name="containers[]"
                                value="{{ $container['handle'] }}"
                                checked
                                class="rounded"
                            >
                            <span class="text-xs">{{ $container['title'] }}</span>
                        </label>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="flex flex-col gap-2 mt-4">
            <button type="submit" formaction="{{ $exportUrl }}" class="btn-primary w-full justify-center" @if($containers->isEmpty()) disabled @endif>
                {{ __('statamic-content-usage::widgets.asset-usage.export_button') }}
            </button>
            <button type="submit" formaction="{{ $exportUnusedUrl }}" class="btn w-full justify-center text-center" @if($containers->isEmpty()) disabled @endif>
                {{ __('statamic-content-usage::widgets.asset-usage.export_unused_button') }}
            </button>
        </div>
    </form>
</div>
