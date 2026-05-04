<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="font-black text-3xl text-gray-900 leading-tight tracking-tight">
                    Travel Orchestration
                </h2>
                <p class="text-sm text-gray-500 font-medium mt-0.5">Smart Weather-Adaptive Trip Planning</p>
            </div>
            <div class="flex items-center space-x-3 bg-white p-2 pr-4 rounded-2xl shadow-sm border border-gray-100">
                @if(auth()->user()->avatar)
                    <img src="{{ Storage::url(auth()->user()->avatar) }}" class="w-10 h-10 rounded-xl object-cover shadow-sm">
                @else
                    <div class="w-10 h-10 bg-gradient-to-br from-indigo-600 to-blue-500 rounded-xl flex items-center justify-center text-white font-black text-lg">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                @endif
                <div>
                    <p class="text-xs text-gray-400 font-bold uppercase tracking-wider">Selamat Datang</p>
                    <p class="text-sm font-bold text-gray-800">{{ auth()->user()->name }}</p>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-12 bg-[#F8FAFC]">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <livewire:dashboard.itineraries />
        </div>
    </div>
</x-app-layout>
