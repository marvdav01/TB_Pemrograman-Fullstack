<?php

use Livewire\Volt\Component;
use App\Models\Itinerary;
use App\Models\ItineraryLog;
use App\Models\WeatherSimulation;
use App\Models\Destination;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;

new class extends Component {
    public $itineraries   = [];
    public $userId;
    public $weatherMap    = []; // ['city_id' => 'condition']
    public $stats         = ['total' => 0, 'planned' => 0, 'changed' => 0];
    public $showLogs      = false;
    public $logs          = [];
    
    // Search & Filter
    public $search        = '';
    public $filterStatus  = 'all';

    // Form Data
    public $editingId      = null;
    public $destination_id = '';
    public $visit_date     = '';
    public $notes          = '';
    public $weatherPreview = null;
    public $showAddModal   = false;
    public $availableDestinations = [];

    public function mount()
    {
        $this->userId = Auth::id();
        $this->loadItineraries();
        $this->availableDestinations = Destination::with('city')->orderBy('name')->get();
    }

    public function loadItineraries()
    {
        $query = Itinerary::with(['destination.city'])
            ->where('user_id', Auth::id());

        if ($this->search) {
            $query->whereHas('destination', function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhereHas('city', function($sq) {
                      $sq->where('name', 'like', '%' . $this->search . '%');
                  });
            });
        }

        if ($this->filterStatus !== 'all') {
            $query->where('status', $this->filterStatus);
        }

        $this->itineraries = $query->orderBy('visit_date', 'asc')->get();

        // Stats are based on ALL itineraries for this user, not just filtered ones?
        // Let's keep stats reflecting the whole picture for context
        $all = Itinerary::where('user_id', Auth::id())->get();
        $this->stats['total']   = $all->count();
        $this->stats['planned'] = $all->where('status', 'planned')->count();
        $this->stats['changed'] = $all->where('status', 'auto_changed')->count();

        // Bangun peta cuaca menggunakan agent yang sama dengan sistem deteksi
        $this->weatherMap = [];
        $agent = app(\App\Services\WeatherAdapterAgent::class);
        foreach ($this->itineraries as $itinerary) {
            $city = $itinerary->destination?->city;
            if ($city) {
                $this->weatherMap[$itinerary->id] = $agent->resolveWeatherCondition($city, $itinerary->visit_date);
            }
        }
    }

    public function updatedSearch() { $this->loadItineraries(); }
    public function updatedFilterStatus() { $this->loadItineraries(); }

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

    public function updatedDestinationId() { $this->fetchWeatherPreview(); }
    public function updatedVisitDate() { $this->fetchWeatherPreview(); }

    public function fetchWeatherPreview()
    {
        if (!$this->destination_id || !$this->visit_date) {
            $this->weatherPreview = null;
            return;
        }

        $dest = Destination::with('city')->find($this->destination_id);
        if ($dest && $dest->city) {
            $agent = app(\App\Services\WeatherAdapterAgent::class);
            $this->weatherPreview = $agent->resolveWeatherCondition($dest->city, $this->visit_date);
        } else {
            $this->weatherPreview = 'unknown';
        }
    }

    public function openAddModal()
    {
        $this->reset(['editingId', 'destination_id', 'notes', 'weatherPreview']);
        $this->visit_date = now()->format('Y-m-d');
        $this->dispatch('open-modal', 'add-trip-modal');
    }

    public function editTrip($id)
    {
        $itinerary = Itinerary::where('id', $id)->where('user_id', Auth::id())->first();
        if ($itinerary) {
            $this->editingId      = $id;
            $this->destination_id = $itinerary->destination_id;
            $this->visit_date     = $itinerary->visit_date;
            $this->notes          = $itinerary->notes;
            $this->fetchWeatherPreview();
            $this->dispatch('open-modal', 'add-trip-modal');
        }
    }

    public function closeAddModal()
    {
        $this->dispatch('close-modal', 'add-trip-modal');
        $this->reset(['editingId', 'destination_id', 'visit_date', 'notes', 'weatherPreview']);
    }

    public function saveTrip()
    {
        $this->validate([
            'destination_id' => 'required|exists:destinations,id',
            'visit_date'     => 'required|date|after_or_equal:today',
            'notes'          => 'nullable|string|max:255',
        ]);

        if ($this->editingId) {
            $itinerary = Itinerary::where('id', $this->editingId)->where('user_id', Auth::id())->first();
            if ($itinerary) {
                $itinerary->update([
                    'destination_id' => $this->destination_id,
                    'visit_date'     => $this->visit_date,
                    'notes'          => $this->notes,
                    // If manually edited, maybe reset status to planned?
                    'status'         => 'planned', 
                ]);
                $msg = '✅ Perjalanan berhasil diperbarui!';
            }
        } else {
            Itinerary::create([
                'user_id'        => Auth::id(),
                'destination_id' => $this->destination_id,
                'visit_date'     => $this->visit_date,
                'notes'          => $this->notes,
                'status'         => 'planned',
            ]);
            $msg = '🚀 Perjalanan baru berhasil ditambahkan!';
        }

        $this->dispatch('close-modal', 'add-trip-modal');
        $this->loadItineraries();
        $this->dispatch('toast', message: $msg, type: 'success');
    }

    public function deleteTrip($id)
    {
        $itinerary = Itinerary::where('id', $id)->where('user_id', Auth::id())->first();
        if ($itinerary) {
            $itinerary->delete();
            $this->loadItineraries();
            $this->dispatch('toast', message: '🗑️ Perjalanan telah dihapus.', type: 'info');
        }
    }

    // Konfirmasi manual override
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
    <div class="flex flex-col md:flex-row gap-4 justify-between items-start md:items-center">
        <div class="flex flex-col sm:flex-row gap-3 w-full md:w-auto">
            <div class="relative group w-full sm:w-64">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </span>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari destinasi atau kota..." 
                       class="block w-full pl-10 pr-3 py-2.5 border border-gray-200 dark:border-gray-700 rounded-2xl bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition shadow-sm">
            </div>
            
            <select wire:model.live="filterStatus" class="block w-full sm:w-44 py-2.5 pl-3 pr-10 border border-gray-200 dark:border-gray-700 rounded-2xl bg-white dark:bg-gray-800 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition shadow-sm">
                <option value="all">Semua Status</option>
                <option value="planned">Planned</option>
                <option value="auto_changed">Agent Changed</option>
                <option value="overridden">Confirmed</option>
            </select>
        </div>

        <div class="flex items-center gap-3 w-full md:w-auto">
            <button wire:click="loadLogs" wire:loading.attr="disabled" class="flex-1 md:flex-none inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl text-sm font-bold text-gray-700 dark:text-gray-200 hover:bg-gray-50 transition shadow-sm group">
                <span wire:loading.remove wire:target="loadLogs" class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-gray-400 group-hover:text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                    Riwayat
                </span>
                <span wire:loading wire:target="loadLogs" class="flex items-center gap-2 text-xs">
                    <svg class="animate-spin h-3 w-3 text-indigo-500" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    Loading
                </span>
            </button>
            <button wire:click="openAddModal" class="flex-1 md:flex-none inline-flex items-center justify-center gap-2 px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 rounded-2xl text-sm font-bold text-white transition shadow-lg shadow-indigo-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                Tambah Trip
            </button>
        </div>
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
                    $weatherCond = $weatherMap[$itinerary->id] ?? '';
                    $weatherIcon = match($weatherCond) {
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
                                        {{ $weatherIcon }} Cuaca: {{ ucfirst($weatherCond ?: '—') }}
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

                            <button wire:click="editTrip({{ $itinerary->id }})" 
                                    class="p-2 text-gray-300 hover:text-indigo-500 transition-colors"
                                    title="Edit Perjalanan">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </button>

                            <button wire:click="deleteTrip({{ $itinerary->id }})" 
                                    wire:confirm="Apakah Anda yakin ingin menghapus perjalanan ini?"
                                    class="p-2 text-gray-300 hover:text-red-500 transition-colors"
                                    title="Hapus Perjalanan">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="flex flex-col items-center justify-center py-20 text-center rounded-3xl border-2 border-dashed border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 px-6">
                    <div class="w-20 h-20 bg-white dark:bg-gray-700 rounded-full flex items-center justify-center text-4xl shadow-sm mb-6">
                        {{ $search ? '🔍' : '🗺️' }}
                    </div>
                    <h3 class="text-xl font-bold text-gray-700 dark:text-white">
                        {{ $search ? 'Tidak ada hasil yang cocok' : 'Belum Ada Itinerary' }}
                    </h3>
                    <p class="text-gray-400 text-sm mt-2 max-w-xs mx-auto leading-relaxed">
                        {{ $search ? "Kami tidak menemukan perjalanan dengan kata kunci '$search'. Coba kata kunci lain." : "Jadwalkan petualangan pertama Anda hari ini!" }}
                    </p>
                    @if(!$search)
                        <button wire:click="openAddModal" class="mt-8 px-8 py-3 bg-indigo-600 text-white rounded-2xl font-bold hover:bg-indigo-700 transition shadow-lg shadow-indigo-100">
                            Mulai Rencana Baru
                        </button>
                    @endif
                </div>
            @endforelse
        </div>
    </div>

    <!-- ====== Add Trip Modal ====== -->
    <x-modal name="add-trip-modal" :show="$showAddModal" on-close="$wire.closeAddModal()" focusable>
        <form wire:submit="saveTrip" class="p-8">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight">
                    {{ $editingId ? '✏️ Edit Perjalanan' : '📅 Tambah Perjalanan' }}
                </h2>
                <button type="button" wire:click="closeAddModal" class="text-gray-400 hover:text-gray-600 transition text-2xl">&times;</button>
            </div>
            
            <p class="text-sm text-gray-500 dark:text-gray-400 -mt-4 mb-8">
                Rencanakan petualangan Anda berikutnya. Sistem akan memantau cuaca secara otomatis.
            </p>

            <div class="space-y-6">
                <!-- Destinasi -->
                <div>
                    <x-input-label for="destination_id" value="Pilih Destinasi" class="text-xs uppercase tracking-widest font-bold text-indigo-600" />
                    <div class="relative mt-1">
                        <select id="destination_id" wire:model.live="destination_id" 
                                class="block w-full border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-2xl shadow-sm text-sm py-3.5 pl-4 transition appearance-none">
                            <option value="">-- Pilih Lokasi Tujuan --</option>
                            @php
                                $grouped = $availableDestinations->groupBy(fn($d) => $d->city?->name ?? 'Lainnya');
                            @endphp
                            @foreach($grouped as $cityName => $dests)
                                <optgroup label="📍 {{ $cityName }}">
                                    @foreach($dests as $dest)
                                        <option value="{{ $dest->id }}">
                                            {{ $dest->type === 'outdoor' ? '🌄' : '🏛️' }} {{ $dest->name }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-400">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </div>
                    </div>
                    <x-input-error :messages="$errors->get('destination_id')" class="mt-2" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Tanggal -->
                    <div>
                        <x-input-label for="visit_date" value="Tanggal Kunjungan" class="text-xs uppercase tracking-widest font-bold text-indigo-600" />
                        <x-text-input id="visit_date" type="date" wire:model.live="visit_date" 
                                     class="mt-1 block w-full py-3.5 rounded-2xl border-gray-200 focus:ring-indigo-500" />
                        <x-input-error :messages="$errors->get('visit_date')" class="mt-2" />
                    </div>

                    <!-- Catatan / Tips -->
                    <div>
                        <x-input-label for="notes" value="Catatan (Opsional)" class="text-xs uppercase tracking-widest font-bold text-indigo-600" />
                        <x-text-input id="notes" type="text" wire:model="notes" placeholder="Contoh: Bawa kamera..."
                                     class="mt-1 block w-full py-3.5 rounded-2xl border-gray-200 focus:ring-indigo-500" />
                        <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                    </div>
                </div>

                <!-- Preview Cuaca Smart -->
                <div x-show="$wire.weatherPreview" 
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 transform scale-95"
                     x-transition:enter-end="opacity-100 transform scale-100"
                     class="overflow-hidden">
                    @if($weatherPreview)
                        <div class="p-5 rounded-[2rem] border-2 flex items-center gap-5 transition-all duration-500
                            {{ $weatherPreview === 'rainy' ? 'bg-blue-50/50 border-blue-200 text-blue-700' : 
                               ($weatherPreview === 'sunny' ? 'bg-amber-50/50 border-amber-200 text-amber-700' : 'bg-emerald-50/50 border-emerald-200 text-emerald-700') }}">
                            <div class="w-16 h-16 rounded-2xl bg-white/80 flex items-center justify-center text-4xl shadow-sm">
                                @if($weatherPreview === 'rainy') 🌧️ @elseif($weatherPreview === 'sunny') ☀️ @elseif($weatherPreview === 'cloudy') ⛅ @else ❓ @endif
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="text-[10px] font-black uppercase tracking-widest px-2 py-0.5 rounded-full bg-white/50 border border-current/20">AI Forecast</span>
                                    @if($weatherPreview === 'rainy')
                                        <span class="text-[10px] font-black uppercase tracking-widest px-2 py-0.5 rounded-full bg-red-100 text-red-600">High Risk</span>
                                    @endif
                                </div>
                                <p class="text-sm font-black leading-tight">
                                    @if($weatherPreview === 'rainy')
                                        Hujan Diprediksi. Kami sarankan membawa perlengkapan hujan.
                                    @elseif($weatherPreview === 'sunny')
                                        Cerah Berawan! Kondisi terbaik untuk perjalanan ini.
                                    @elseif($weatherPreview === 'cloudy')
                                        Berawan Sejuk. Sangat nyaman untuk eksplorasi.
                                    @elseif($weatherPreview === 'unknown')
                                        Data cuaca belum tersedia.
                                    @endif
                                </p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="mt-10 flex flex-col sm:flex-row justify-end gap-3">
                <x-secondary-button type="button" wire:click="closeAddModal" class="rounded-2xl px-6 py-3.5 border-none bg-gray-100 hover:bg-gray-200 text-gray-600 font-bold justify-center">
                    Batal
                </x-secondary-button>

                <x-primary-button class="rounded-2xl px-10 py-3.5 bg-indigo-600 hover:bg-indigo-700 shadow-xl shadow-indigo-200 justify-center">
                    <span wire:loading.remove wire:target="saveTrip">Simpan Jadwal</span>
                    <span wire:loading wire:target="saveTrip" class="flex items-center gap-2">
                        <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        Menyimpan...
                    </span>
                </x-primary-button>
            </div>
        </form>
    </x-modal>
</div>
