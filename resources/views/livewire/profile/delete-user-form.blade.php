<?php

use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component
{
    public string $password = '';

    /**
     * Delete the currently authenticated user.
     */
    public function deleteUser(Logout $logout): void
    {
        $this->validate([
            'password' => ['required', 'string', 'current_password'],
        ]);

        tap(Auth::user(), $logout(...))->delete();

        $this->redirect('/', navigate: true);
    }
}; ?>

<section class="space-y-6">
    <header>
        <h2 class="text-xl font-black text-rose-600 tracking-tight">
            Hapus Akun
        </h2>
        <p class="mt-1 text-sm text-gray-500">
            Tindakan ini bersifat permanen. Semua data Anda akan dihapus selamanya.
        </p>
    </header>

    <div class="p-4 bg-rose-50 rounded-2xl border border-rose-100">
        <p class="text-xs text-rose-700 leading-relaxed font-medium">
            ⚠️ <strong>Perhatian:</strong> Setelah akun dihapus, tidak ada cara untuk mengembalikan data Anda. Harap berhati-hati.
        </p>
    </div>

    <x-danger-button
        class="!rounded-2xl !px-6 !py-3 shadow-lg shadow-rose-100"
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
    >{{ __('Hapus Akun Sekarang') }}</x-danger-button>

    <x-modal name="confirm-user-deletion" :show="$errors->isNotEmpty()" focusable>
        <form wire:submit="deleteUser" class="p-8 bg-white">
            <div class="flex items-center gap-4 mb-6">
                <div class="w-12 h-12 bg-rose-100 rounded-2xl flex items-center justify-center text-rose-600 text-2xl">⚠️</div>
                <div>
                    <h2 class="text-xl font-black text-gray-900">Konfirmasi Penghapusan</h2>
                    <p class="text-sm text-gray-500">Apakah Anda yakin ingin menghapus akun ini?</p>
                </div>
            </div>

            <p class="text-sm text-gray-600 leading-relaxed">
                Silakan masukkan password Anda untuk mengonfirmasi bahwa Anda ingin menghapus akun secara permanen.
            </p>

            <div class="mt-6">
                <x-input-label for="password" value="{{ __('Password') }}" class="sr-only" />

                <x-text-input
                    wire:model="password"
                    id="password"
                    name="password"
                    type="password"
                    class="mt-1 block w-full !bg-gray-50 !border-gray-200 rounded-2xl px-4 py-3"
                    placeholder="{{ __('Masukkan password Anda untuk konfirmasi') }}"
                />

                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div class="mt-8 flex justify-end gap-3">
                <x-secondary-button x-on:click="$dispatch('close')" class="!rounded-2xl !px-6">
                    {{ __('Batal') }}
                </x-secondary-button>

                <x-danger-button class="!rounded-2xl !px-6">
                    {{ __('Ya, Hapus Akun') }}
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</section>
