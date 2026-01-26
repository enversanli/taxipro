<x-filament-panels::page>

    @foreach($this->getGroups() as $groupName => $platforms)
        <div class="mb-6">
            <h3 class="text-lg font-bold text-gray-500 mb-3 uppercase tracking-wider">{{ $groupName }}</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($platforms as $platform)
                    @php
                        $isConnected = $this->connections[$platform['id']] ?? false;
                        $colorClass = match($platform['color']) {
                            'success' => 'text-green-600 bg-green-50 ring-green-600/20',
                            'info' => 'text-blue-600 bg-blue-50 ring-blue-600/20',
                            'warning' => 'text-amber-600 bg-amber-50 ring-amber-600/20',
                            'danger' => 'text-red-600 bg-red-50 ring-red-600/20',
                            default => 'text-gray-600 bg-gray-50 ring-gray-600/20',
                        };
                    @endphp

                    <div class="p-4 bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm flex items-center justify-between">

                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-lg flex items-center justify-center {{ $colorClass }} ring-1">
                                <x-filament::icon
                                    icon="{{ $platform['icon'] }}"
                                    class="h-6 w-6"
                                />
                            </div>

                            <div>
                                <h4 class="font-bold text-gray-900 dark:text-white">{{ $platform['name'] }}</h4>
                                <div class="flex items-center gap-1.5 mt-0.5">
                                    <div class="h-2 w-2 rounded-full {{ $isConnected ? 'bg-green-500' : 'bg-gray-300' }}"></div>
                                    <span class="text-xs font-medium text-gray-500">
                                        {{ $isConnected ? 'Verbunden' : 'Nicht verbunden' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{ ($this->connectAction)(['platform' => $platform['id']]) }}
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach

</x-filament-panels::page>
