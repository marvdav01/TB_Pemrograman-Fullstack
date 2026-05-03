<?php

use Livewire\Volt\Component;
use App\Models\Itinerary;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;

new class extends Component {
    public $itineraries = [];
    public $userId;
    public $stats = [
        'total' => 0,
        'planned' => 0,
        'changed' => 0
    ];

    public function mount()
    {
        $this->userId = Auth::id();
        $this->loadItineraries();
    }

    public function loadItineraries()
    {
        $this->itineraries = Itinerary::with('destination')
            ->where('user_id', Auth::id())
            ->orderBy('visit_date', 'asc')
            ->get();
        
        $this->stats['total'] = $this->itineraries->count();
        $this->stats['planned'] = $this->itineraries->where('status', 'planned')->count();
        $this->stats['changed'] = $this->itineraries->where('status', 'auto_changed')->count();
    }

    #[On('echo-private:user.{userId},ItineraryAutoChanged')]
    public function handleAutoChange($event)
    {
        $this->loadItineraries();
    }
}; ?>

<div class="space-y-8" 
     x-data="{ showToast: false, toastMessage: '' }"
     x-on:echo-private:user.{{ auth()->id() }},ItineraryAutoChanged.window="
        showToast = true;
        toastMessage = $event.detail.message;
        setTimeout(() => showToast = false, 5000);
     ">
    
    <!-- Real-time Toast Notification -->
    <div x-show="showToast" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-[-20px] scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-300"
         x-transition:leave-start="opacity-100 translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 translate-y-[-20px] scale-95"
         class="fixed top-5 right-5 z-50 bg-white/80 backdrop-blur-md border border-blue-200 text-gray-800 px-6 py-4 rounded-2xl shadow-[0_20px_50px_rgba(8,_112,_184,_0.2)] flex items-center space-x-4 max-w-md"
         style="display: none;">
        <div class="flex-shrink-0 w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center text-white shadow-lg shadow-blue-200">
            <svg class="w-6 h-6 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        </div>
        <div>
            <p class="font-bold text-blue-900">Pemberitahuan Sistem</p>
            <p x-text="toastMessage" class="text-sm text-blue-700"></p>
        </div>
        <button @click="showToast = false" class="text-gray-400 hover:text-gray-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
    </div>

    <!-- Stats Section -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Total Perjalanan</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ $stats['total'] }}</p>
                </div>
                <div class="p-3 bg-indigo-50 dark:bg-indigo-900/30 rounded-2xl text-indigo-600 dark:text-indigo-400">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-1.447-.894L15 7m0 10V7"></path></svg>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Terjadwal</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ $stats['planned'] }}</p>
                </div>
                <div class="p-3 bg-emerald-50 dark:bg-emerald-900/30 rounded-2xl text-emerald-600 dark:text-emerald-400">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Dialihkan Otomatis</p>
                    <p class="text-3xl font-bold text-blue-600 dark:text-blue-400">{{ $stats['changed'] }}</p>
                </div>
                <div class="p-3 bg-blue-50 dark:bg-blue-900/30 rounded-2xl text-blue-600 dark:text-blue-400">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.691.346a6 6 0 01-3.86.517l-2.387-.477a2 2 0 00-1.022.547l-1.168 1.168a2 2 0 00.556 3.27l1.057.423a6 6 0 005.152 0l1.057-.423a2 2 0 00.556-3.27l-1.168-1.168z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] shadow-xl shadow-gray-200/50 dark:shadow-none border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="p-8 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight">Timeline Perjalanan</h2>
                <p class="text-sm text-gray-500 mt-1">Kelola dan pantau aktivitas travel Anda secara real-time.</p>
            </div>
            <div class="flex items-center space-x-2">
                <span class="flex h-3 w-3 relative">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3 bg-blue-500"></span>
                </span>
                <span class="text-sm font-bold text-blue-600 dark:text-blue-400 uppercase tracking-widest">Active Monitoring</span>
            </div>
        </div>

        <div class="p-8">
            <div class="space-y-6">
                @forelse($itineraries as $itinerary)
                    <div class="group relative bg-white dark:bg-gray-800 border-2 rounded-3xl p-6 transition-all duration-500 hover:scale-[1.02] hover:shadow-2xl hover:shadow-blue-200/40 {{ $itinerary->status === 'auto_changed' ? 'border-blue-500/20 bg-blue-50/10' : 'border-gray-100 dark:border-gray-700 hover:border-indigo-500/20' }}">
                        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                            <div class="flex items-center space-x-6">
                                <!-- Destination Icon -->
                                <div class="relative">
                                    <div class="w-16 h-16 rounded-2xl flex items-center justify-center transition-transform group-hover:rotate-6 {{ $itinerary->destination->type === 'outdoor' ? 'bg-orange-100 text-orange-600 dark:bg-orange-900/30 dark:text-orange-400' : 'bg-indigo-100 text-indigo-600 dark:bg-indigo-900/30 dark:text-indigo-400' }}">
                                        @if($itinerary->destination->type === 'outdoor')
                                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707m12.728 0l-.707-.707M6.343 6.343l-.707-.707M12 8a4 4 0 100 8 4 4 0 000-8z"></path></svg>
                                        @else
                                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                        @endif
                                    </div>
                                    @if($itinerary->status === 'auto_changed')
                                        <div class="absolute -top-2 -right-2 bg-blue-600 text-white p-1 rounded-lg shadow-lg border-2 border-white dark:border-gray-800">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                        </div>
                                    @endif
                                </div>

                                <!-- Text Details -->
                                <div>
                                    <h3 class="text-xl font-bold text-gray-900 dark:text-white group-hover:text-blue-600 transition-colors">{{ $itinerary->destination->name }}</h3>
                                    <div class="flex flex-wrap items-center gap-4 mt-2">
                                        <div class="flex items-center text-sm text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700/50 px-3 py-1 rounded-full">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                            {{ \Carbon\Carbon::parse($itinerary->visit_date)->translatedFormat('l, d F Y') }}
                                        </div>
                                        <div class="flex items-center text-sm text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700/50 px-3 py-1 rounded-full">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                            Lokasi ID: #{{ $itinerary->destination->city_id }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="flex flex-col items-end gap-3">
                                @if($itinerary->status === 'auto_changed')
                                    <div class="flex flex-col items-end">
                                        <span class="inline-flex items-center px-6 py-2.5 rounded-2xl text-sm font-black bg-gradient-to-r from-blue-600 to-blue-400 text-white shadow-lg shadow-blue-200/60 uppercase tracking-widest">
                                            <svg class="w-4 h-4 mr-2 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            Weather Adjusted
                                        </span>
                                        <p class="text-[10px] text-blue-500 font-bold mt-2 uppercase tracking-tighter">Dialihkan otomatis karena hujan</p>
                                    </div>
                                @else
                                    <span class="inline-flex items-center px-6 py-2.5 rounded-2xl text-sm font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-800 uppercase tracking-widest">
                                        Confirmed
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="flex flex-col items-center justify-center py-20 text-center bg-gray-50 dark:bg-gray-800/50 rounded-[2rem] border-2 border-dashed border-gray-200 dark:border-gray-700">
                        <div class="w-24 h-24 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mb-6">
                            <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">Belum Ada Jadwal</h3>
                        <p class="text-gray-500 max-w-xs mt-2">Mulai petualangan Anda dengan menambahkan destinasi baru ke itinerary.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
