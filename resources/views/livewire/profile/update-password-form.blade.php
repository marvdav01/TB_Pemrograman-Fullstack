<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Volt\Component;

new class extends Component
{
    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Update the password for the currently authenticated user.
     */
    public function updatePassword(): void
    {
        try {
            $validated = $this->validate([
                'current_password' => ['required', 'string', 'current_password'],
                'password' => ['required', 'string', Password::defaults(), 'confirmed'],
            ]);
        } catch (ValidationException $e) {
            $this->reset('current_password', 'password', 'password_confirmation');

            throw $e;
        }

        Auth::user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        $this->reset('current_password', 'password', 'password_confirmation');

        $this->dispatch('password-updated');
        $this->dispatch('toast', message: '🔐 Password berhasil diperbarui!', type: 'success');
    }
}; ?>

<section class="space-y-6">
    <header>
        <h2 class="text-xl font-black text-gray-900 dark:text-white tracking-tight">
            Keamanan Password
        </h2>
        <p class="mt-1 text-sm text-gray-500">
            Pastikan akun Anda menggunakan kata sandi yang panjang dan acak untuk menjaga keamanan.
        </p>
    </header>

    <form wire:submit="updatePassword" class="mt-6 space-y-6">
        <div class="space-y-2">
            <x-input-label for="update_password_current_password" :value="__('Password Saat Ini')" class="font-bold text-gray-700 dark:text-gray-300" />
            <x-text-input wire:model="current_password" id="update_password_current_password" name="current_password" type="password" class="mt-1 block w-full !bg-gray-50 !border-gray-200 !text-gray-900 focus:!ring-indigo-500 focus:!border-indigo-500 rounded-2xl px-4 py-3" autocomplete="current-password" placeholder="••••••••" />
            <x-input-error :messages="$errors->get('current_password')" class="mt-2" />
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-2">
                <x-input-label for="update_password_password" :value="__('Password Baru')" class="font-bold text-gray-700 dark:text-gray-300" />
                <x-text-input wire:model="password" id="update_password_password" name="password" type="password" class="mt-1 block w-full !bg-gray-50 !border-gray-200 !text-gray-900 focus:!ring-indigo-500 focus:!border-indigo-500 rounded-2xl px-4 py-3" autocomplete="new-password" placeholder="Min. 8 karakter" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div class="space-y-2">
                <x-input-label for="update_password_password_confirmation" :value="__('Konfirmasi Password')" class="font-bold text-gray-700 dark:text-gray-300" />
                <x-text-input wire:model="password_confirmation" id="update_password_password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full !bg-gray-50 !border-gray-200 !text-gray-900 focus:!ring-indigo-500 focus:!border-indigo-500 rounded-2xl px-4 py-3" autocomplete="new-password" placeholder="Ulangi password baru" />
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
            </div>
        </div>

        <div class="flex items-center gap-4 pt-2">
            <x-primary-button class="!px-8 !py-3 !rounded-2xl !bg-indigo-600 hover:!bg-indigo-700 shadow-lg shadow-indigo-100 transition-all active:scale-95">
                {{ __('Update Password') }}
            </x-primary-button>

            <x-action-message class="text-emerald-600 font-bold" on="password-updated">
                {{ __('Password Berhasil Diubah!') }}
            </x-action-message>
        </div>
    </form>
</section>
