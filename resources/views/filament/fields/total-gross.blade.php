
<h3 class="mb-6 text-center text-2xl font-bold text-gray-800">
    {{ $title }}
</h3>

    <div class="grid gap-4 md:grid-cols-3 lg:grid-cols-3">
        @foreach ($items as $item)
        <x-filament::card>
            <h2 class="text-lg font-bold">{{ __('common.' . $item[0]) }}</h2>
            <p>€ {{ number_format($item[1], 2) }}</p>
        </x-filament::card>
        @endforeach
    </div>

{{--
<div class="flex flex-wrap justify-center gap-6 text-center">
    @foreach ($items as $item)
        <div class="w-44 h-28 flex flex-col justify-center items-center px-4 py-3 rounded-xl shadow bg-primary-600 text-white">
            <small class="text-sm font-medium tracking-wide uppercase text-white/90">
                {{ __('common.' . $item[0]) }}
            </small>
            <span class="mt-1 text-xl font-semibold">
                € {{ number_format($item[1], 2) }}
            </span>
        </div>
    @endforeach
</div>
 --}}

@if (!empty($details))

    <div class="mt-22">
        <h4 class="mb-4 text-center text-xl font-semibold text-gray-700">
            Platforms
        </h4>

        <div class="flex flex-wrap justify-center gap-6 text-center">
            @foreach ($details as $platform => $values)

                <div class="w-56 p-4 rounded-xl shadow bg-primary-50 text-primary-900 border border-primary-200">
                    <h5 class="text-lg font-semibold mb-2">
                        {{ isset($values['platform']) ? ucfirst($values['platform']) : ucfirst($platform) }}
                    </h5>
                    <ul class="text-sm space-y-1">
                        <li><strong>Gross:</strong> €{{ number_format($values['gross'], 2) }}</li>
                        <li><strong>Bar:</strong> €{{ number_format($values['bar'], 2) }}</li>
                        <li><strong>Tip:</strong> €{{ number_format($values['tip'], 2) }}</li>
                        <li><strong>Cash:</strong> €{{ number_format($values['cash'], 2) }}</li>
                        <li><strong>Net:</strong> €{{ number_format($values['net'], 2) }}</li>
                    </ul>
                </div>
            @endforeach
        </div>
    </div>
@endif
