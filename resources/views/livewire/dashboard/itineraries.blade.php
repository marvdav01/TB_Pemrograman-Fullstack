<?php

use Livewire\Volt\Component;
use App\Models\Itinerary;
use App\Models\ItineraryLog;
use App\Models\WeatherSimulation;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;

new class extends Component {
    public $itineraries   = [];
    public $userId;
    public $weatherMap    = []; // ['city_id' => 'condition']
    public $stats         = ['total' => 0, 'planned' => 0, 'changed' => 0];
    public $showLogs      = false;
    public $logs          = [];
    public $overrideId    = null;  // ID itinerary yang sedang di-override

    public function mount()
    {
        $this->userId = Auth::id();
        $this->loadItineraries();
    }

    public function loadItineraries()
    {
        $this->itineraries = Itinerary::with(['destination.city'])
            ->where('user_id', Auth::id())
            ->orderBy('visit_date', 'asc')
            ->get();

        $this->stats['total']   = $this->itineraries->count();
        $this->stats['planned'] = $this->itineraries->where('status', 'planned')->count();
        $this->stats['changed'] = $this->itineraries->where('status', 'auto_changed')->count();

        // Bangun peta cuaca hari ini dari database simulasi
        $today = now()->format('Y-m-d');
        $cityIds = $this->itineraries->pluck('destination.city_id')->unique()->filter();
        $weathers = WeatherSimulation::whereIn('city_id', $cityIds)->where('date', $today)->get();
        foreach ($weathers as $w) {
            $this->weatherMap[$w->city_id] = $w->condition;
        }
    }

    public function loadLogs()
    {
        $this->logs = ItineraryLog::with(['itinerary'])
            ->where('user_id', Auth::id())
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();
        $this->showLogs = true;
    }

    public function hideLogs()
    {
        $this->showLogs = false;
    }

    // Konfirmasi manual override: user tidak setuju dan ingin reset ke planned
    public function override($itineraryId)
    {
        $itinerary = Itinerary::where('id', $itineraryId)
            ->where('user_id', Auth::id())
            ->first();

        if ($itinerary) {
            $itinerary->update(['status' => 'overridden']);
            $this->loadItineraries();
            $this->dispatch('toast', message: '✅ Perubahan otomatis telah Anda konfirmasi.', type: 'success');
        }
    }

    #[On('echo-private:user.{userId},ItineraryAutoChanged')]
    public function handleAutoChange($event)
    {
        $this->loadItineraries();
    }
}; ?>

<div class="space-y-8"
     x-data="{
        showToast: false,
        toastMessage: '',
        toastType: 'info',
        showWeather: false,
        toast(msg, type = 'info') {
            this.toastMessage = msg;
            this.toastType = type;
            this.showToast = true;
            setTimeout(() => this.showToast = false, 6000);
        }
     }"
     x-on:echo-private:user.{{ auth()->id() }},ItineraryAutoChanged.window="
        toast($event.detail.message, 'warning');
        $wire.loadItineraries();
     "
     x-on:toast.window="toast($event.detail.message, $event.detail.type || 'info')">

    <!-- ====== Toast Notification ====== -->
    <div x-show="showToast"
         x-transition:enter="transition ease-out duration-400"
         x-transition:enter-start="opacity-0 translate-y-[-20px]"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed top-5 right-5 z-50 max-w-md"
         style="display:none;">
        <div :class="{
            'bg-amber-500 border-amber-400': toastType === 'warning',
            'bg-emerald-500 border-emerald-400': toastType === 'success',
            'bg-blue-500 border-blue-400': toastType === 'info'
         }" class="text-white px-6 py-4 rounded-2xl shadow-2xl flex items-start space-x-4 border-l-4">
            <span class="text-2xl mt-0.5">
                <template x-if="toastType === 'warning'">🌧️</template>
                <template x-if="toastType === 'success'">✅</template>
                <template x-if="toastType === 'info'">ℹ️</template>
            </span>
            <div class="flex-1">
                <p class="font-bold text-sm">Pemberitahuan Sistem</p>
                <p x-text="toastMessage" class="text-sm opacity-90 mt-0.5"></p>
            </div>
            <button @click="showToast = false" class="opacity-70 hover:opacity-100 text-xl leading-none">&times;</button>
        </div>
    </div>

    <!-- ====== Stat Cards ====== -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6">
        <div class="bg-gradient-to-br from-indigo-600 to-indigo-500 p-5 md:p-6 rounded-3xl text-white shadow-lg shadow-indigo-200/60">
            <p class="text-indigo-200 text-[10px] md:text-xs font-bold uppercase tracking-widest">Total Trip</p>
            <p class="text-3xl md:text-4xl font-black mt-1">{{ $stats['total'] }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 p-5 md:p-6 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-sm">
            <p class="text-gray-400 text-[10px] md:text-xs font-bold uppercase tracking-widest">Planned</p>
            <p class="text-3xl md:text-4xl font-black text-emerald-600 mt-1">{{ $stats['planned'] }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 p-5 md:p-6 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-sm">
            <p class="text-gray-400 text-[10px] md:text-xs font-bold uppercase tracking-widest">Adjusted</p>
            <p class="text-3xl md:text-4xl font-black text-amber-500 mt-1">{{ $stats['changed'] }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 p-5 md:p-6 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-sm">
            <p class="text-gray-400 text-[10px] md:text-xs font-bold uppercase tracking-widest">Progress</p>
            <div class="mt-3">
                @php $progress = $stats['total'] > 0 ? (($stats['total'] - $stats['planned']) / $stats['total']) * 100 : 0; @endphp
                <div class="flex items-center justify-between mb-1">
                    <span class="text-[10px] font-bold text-indigo-600">{{ round($progress) }}%</span>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-1.5 dark:bg-gray-700">
                    <div class="bg-indigo-600 h-1.5 rounded-full transition-all duration-1000" style="width: {{ $progress }}%"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- ====== Kontrol Halaman ====== -->
    <div class="flex flex-col sm:flex-row gap-3">
        <button wire:click="loadLogs" wire:loading.attr="disabled" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-sm font-bold text-gray-700 dark:text-gray-200 hover:bg-gray-50 transition relative group">
            <span wire:loading.remove wire:target="loadLogs" class="flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                Riwayat Perubahan
            </span>
            <span wire:loading wire:target="loadLogs" class="flex items-center gap-2">
                <svg class="animate-spin h-4 w-4 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                Memuat...
            </span>
        </button>
        <a href="{{ url('/agent') }}" wire:navigate class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 rounded-xl text-sm font-bold text-white transition shadow-lg shadow-indigo-200">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
            Jalankan Agent Cuaca
        </a>
    </div>

    <!-- ====== Audit Log Panel ====== -->
    @if($showLogs)
    <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="px-8 py-5 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
            <h3 class="text-lg font-black text-gray-900 dark:text-white">📋 Riwayat Perubahan Otomatis</h3>
            <button wire:click="hideLogs" class="text-gray-400 hover:text-gray-600">&times;</button>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-900/50 text-xs text-gray-500 uppercase tracking-widest">
                        <th class="px-6 py-3 text-left">Tanggal</th>
                        <th class="px-6 py-3 text-left">Kota</th>
                        <th class="px-6 py-3 text-left">Dari</th>
                        <th class="px-6 py-3 text-left">Ke</th>
                        <th class="px-6 py-3 text-left">Cuaca</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($logs as $log)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                        <td class="px-6 py-3 font-medium text-gray-700 dark:text-gray-300">{{ $log->visit_date }}</td>
                        <td class="px-6 py-3 text-gray-600 dark:text-gray-400">{{ $log->city_name }}</td>
                        <td class="px-6 py-3 text-red-500 line-through">{{ $log->old_destination }}</td>
                        <td class="px-6 py-3 text-emerald-600 font-bold">{{ $log->new_destination }}</td>
                        <td class="px-6 py-3">
                            <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-700">
                                🌧️ {{ $log->weather_condition }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-6 py-8 text-center text-gray-400 italic">Belum ada riwayat perubahan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <!-- ====== Daftar Itinerary ====== -->
    <div class="bg-white dark:bg-gray-800 rounded-[2rem] shadow-xl shadow-gray-200/40 dark:shadow-none border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="px-8 py-6 border-b border-gray-100 dark:border-gray-700 bg-gray-50/40 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-xl font-black text-gray-900 dark:text-white tracking-tight">Timeline Perjalanan</h2>
                <p class="text-sm text-gray-400 mt-0.5">Dipantau secara real-time oleh Weather-Adaptive Agent</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="flex h-2 w-2 relative">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                </span>
                <span class="text-xs font-bold text-emerald-600 uppercase tracking-widest">Live</span>
            </div>
        </div>

        <div class="p-6 md:p-8 space-y-4">
            @forelse($itineraries as $itinerary)
                @php
                    $isChanged   = $itinerary->status === 'auto_changed';
                    $isOverridden = $itinerary->status === 'overridden';
                    $destType    = $itinerary->destination->type;
                    $city        = $itinerary->destination->city;
                    $weatherIcon = match($weatherMap[$city?->id] ?? '') {
                        'rainy'  => '🌧️', 'cloudy' => '⛅', 'sunny' => '☀️', default => '—'
                    };
                @endphp
                <div class="group relative p-6 rounded-2xl border-2 transition-all duration-300 hover:shadow-lg
                    {{ $isChanged ? 'border-amber-300 bg-amber-50/40 dark:bg-amber-900/10 dark:border-amber-700' : ($isOverridden ? 'border-gray-200 bg-gray-50/50 dark:border-gray-700 dark:bg-gray-900/20' : 'border-gray-100 dark:border-gray-700 hover:border-indigo-200') }}">

                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-5">
                        <!-- Info Destinasi -->
                        <div class="flex items-center gap-5">
                            <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-2xl shrink-0
                                {{ $destType === 'outdoor' ? 'bg-orange-100' : 'bg-indigo-100' }}">
                                {{ $destType === 'outdoor' ? '🌄' : '🏛️' }}
                            </div>
                            <div>
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ $itinerary->destination->name }}</h3>
                                    @if($isChanged)
                                        <span class="text-[10px] bg-amber-100 text-amber-700 font-black px-2 py-0.5 rounded-full uppercase tracking-wider animate-pulse">Agent Changed</span>
                                    @endif
                                </div>
                                <div class="flex flex-wrap items-center gap-x-3 gap-y-1 mt-1 text-sm text-gray-500 dark:text-gray-400">
                                    <span class="flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                        {{ \Carbon\Carbon::parse($itinerary->visit_date)->translatedFormat('l, d M Y') }}
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path></svg>
                                        {{ $city?->name ?? '—' }}
                                    </span>
                                    <span class="flex items-center gap-1">
                                        {{ $weatherIcon }} Cuaca: {{ $weatherMap[$city?->id] ?? '—' }}
                                    </span>
                                    @if($itinerary->notes)
                                        <span class="text-xs text-amber-600 italic">{{ $itinerary->notes }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Status & Aksi -->
                            <div class="flex items-center gap-3 shrink-0">
                                @if($isChanged)
                                    <button wire:click="override({{ $itinerary->id }})"
                                        wire:loading.attr="disabled"
                                        wire:target="override({{ $itinerary->id }})"
                                        class="text-xs font-bold text-amber-600 border border-amber-300 px-4 py-2 rounded-xl hover:bg-amber-50 transition flex items-center gap-2">
                                        <span wire:loading.remove wire:target="override({{ $itinerary->id }})">✅ Terima</span>
                                        <span wire:loading wire:target="override({{ $itinerary->id }})">
                                            <svg class="animate-spin h-3 w-3" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                        </span>
                                    </button>
                                    <span class="inline-flex items-center px-4 py-2 rounded-xl text-xs font-black bg-amber-500 text-white uppercase tracking-wider shadow-lg shadow-amber-200/50">
                                        🌧️ Dialihkan
                                    </span>
                            @elseif($isOverridden)
                                <span class="inline-flex items-center px-4 py-2 rounded-xl text-xs font-bold bg-gray-200 text-gray-600 dark:bg-gray-700 dark:text-gray-300 uppercase tracking-wider">
                                    ✅ Dikonfirmasi
                                </span>
                            @else
                                <span class="inline-flex items-center px-4 py-2 rounded-xl text-xs font-bold bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400 uppercase tracking-wider">
                                    ✓ Planned
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="flex flex-col items-center justify-center py-16 text-center rounded-2xl border-2 border-dashed border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
                    <div class="text-5xl mb-4">🗺️</div>
                    <h3 class="text-xl font-bold text-gray-700 dark:text-white">Belum Ada Itinerary</h3>
                    <p class="text-gray-400 text-sm mt-2 max-w-xs">Jalankan seeder untuk mendapatkan data contoh, atau tambahkan perjalanan baru.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
