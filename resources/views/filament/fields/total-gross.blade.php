<div class="flex flex-wrap gap-4">
    <div class="px-5 py-3 rounded-lg shadow-md bg-primary-600 text-white text-lg font-semibold font-sans">
        <small class="block text-sm font-normal">Total Gross:</small>
        € {{ number_format($value, 2) }}
    </div>

    <div class="px-5 py-3 rounded-lg shadow-md bg-primary-600 text-white text-lg font-semibold font-sans">
        <small class="block text-sm font-normal">Total Tip:</small>
        € {{ number_format($tip, 2) }}
    </div>

    <div class="px-5 py-3 rounded-lg shadow-md bg-primary-600 text-white text-lg font-semibold font-sans">
        <small class="block text-sm font-normal">Total Cash:</small>
        € {{ number_format($cash, 2) }}
    </div>

    <div class="px-5 py-3 rounded-lg shadow-md bg-primary-600 text-white text-lg font-semibold font-sans">
        <small class="block text-sm font-normal">Total Net:</small>
        € {{ number_format($net, 2) }}
    </div>
</div>
