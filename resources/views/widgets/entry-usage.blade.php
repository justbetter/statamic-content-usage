<div class="p-4">
    <h3 class="font-bold text-base mb-2">{{ __('statamic-content-usage::widgets.entry-usage.heading') }}</h3>
    <p class="text-xs mb-4">{{ __('statamic-content-usage::widgets.entry-usage.description') }}</p>

    <form method="GET" action="{{ $exportUrl }}" class="space-y-4">
        <div>
            <label for="collection" class="block text-xs font-bold mb-1">
                {{ __('statamic-content-usage::widgets.entry-usage.collection_label') }}
            </label>
            <select
                id="collection"
                name="collection"
                required
                class="input-text w-full"
            >
                <option value="">{{ __('statamic-content-usage::widgets.entry-usage.select_collection') }}</option>
                @foreach ($collections as $collection)
                    <option value="{{ $collection['handle'] }}">{{ $collection['title'] }}</option>
                @endforeach
            </select>
        </div>

        <div class="mt-2">
            <select
                id="export_type"
                name="export_type"
                required
                class="input-text w-full"
            >
                <option value="used" selected>{{ __('statamic-content-usage::widgets.entry-usage.export_used_button') }}</option>
                <option value="unused">{{ __('statamic-content-usage::widgets.entry-usage.export_unused_button') }}</option>
            </select>
        </div>

        <button type="submit" class="btn-primary w-full justify-center mt-4">
            {{ __('statamic-content-usage::widgets.entry-usage.export_button') }}
        </button>
    </form>
</div>
