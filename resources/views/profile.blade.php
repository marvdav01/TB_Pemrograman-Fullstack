<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="font-black text-3xl text-gray-900 leading-tight tracking-tight">
                    Pengaturan Profil
                </h2>
                <p class="text-sm text-gray-500 font-medium mt-0.5">Kelola informasi identitas dan keamanan akun Anda</p>
            </div>
            <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 text-sm font-bold text-indigo-600 hover:underline">
                ← Kembali ke Dashboard
            </a>
        </div>
    </x-slot>

    <div class="py-12 bg-[#F8FAFC]">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-10">
            <!-- Informasi Profil -->
            <div class="p-8 bg-white rounded-[2.5rem] shadow-xl shadow-gray-200/40 border border-gray-100 overflow-hidden relative">
                <div class="absolute top-0 right-0 w-32 h-32 bg-indigo-50 rounded-bl-[5rem] -mr-10 -mt-10 opacity-50"></div>
                <div class="relative z-10">
                    <livewire:profile.update-profile-information-form />
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
                <!-- Keamanan Password -->
                <div class="p-8 bg-white rounded-[2.5rem] shadow-xl shadow-gray-200/40 border border-gray-100">
                    <livewire:profile.update-password-form />
                </div>

                <!-- Hapus Akun -->
                <div class="p-8 bg-white rounded-[2.5rem] shadow-xl shadow-gray-200/40 border border-gray-100 border-rose-50">
                    <livewire:profile.delete-user-form />
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
