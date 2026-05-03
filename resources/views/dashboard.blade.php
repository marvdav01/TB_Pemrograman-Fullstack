<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="font-black text-3xl text-gray-900 leading-tight tracking-tight">
                    {{ __('Travel Orchestration') }}
                </h2>
                <p class="text-sm text-gray-500 font-medium">Smart Weather-Adaptive Trip Planning</p>
            </div>
            <div class="flex items-center space-x-3 bg-white p-2 rounded-2xl shadow-sm border border-gray-100">
                <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center text-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                </div>
                <div class="pr-2">
                    <p class="text-xs text-gray-400 font-bold uppercase tracking-wider">Authenticated User</p>
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
