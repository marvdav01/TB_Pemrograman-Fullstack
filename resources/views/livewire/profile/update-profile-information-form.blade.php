<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new class extends Component
{
    use WithFileUploads;

    public string $name = '';
    public string $email = '';
    public $avatar;
    public $currentAvatar;

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $user = Auth::user();
        $this->name = $user->name;
        $this->email = $user->email;
        $this->currentAvatar = $user->avatar;
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
            'avatar' => ['nullable', 'image', 'max:2048'], // 2MB Max
        ]);

        if ($this->avatar) {
            // Delete old avatar if exists
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            
            $path = $this->avatar->store('avatars', 'public');
            $user->avatar = $path;
        }

        $user->name = $this->name;
        $user->email = $this->email;

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $this->currentAvatar = $user->avatar;
        $this->avatar = null;

        $this->dispatch('profile-updated', name: $user->name);
        $this->dispatch('toast', message: '✅ Profil berhasil diperbarui!', type: 'success');
    }

    /**
     * Send an email verification notification to the current user.
     */
    public function sendVerification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));

            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }
}; ?>

<section class="space-y-6">
    <header>
        <h2 class="text-xl font-black text-gray-900 dark:text-white tracking-tight">
            Informasi Profil
        </h2>
        <p class="mt-1 text-sm text-gray-500">
            Perbarui informasi profil akun dan foto Anda untuk tampilan yang lebih personal.
        </p>
    </header>

    <form wire:submit="updateProfileInformation" class="mt-6 space-y-8">
        <!-- Avatar Upload -->
        <div class="flex flex-col sm:flex-row items-center gap-6">
            <div class="relative group">
                <div class="w-32 h-32 rounded-[2rem] overflow-hidden border-4 border-white dark:border-gray-700 shadow-xl relative bg-gray-100">
                    @if ($avatar)
                        <img src="{{ $avatar->temporaryUrl() }}" class="w-full h-full object-cover">
                    @elseif ($currentAvatar)
                        <img src="{{ Storage::url($currentAvatar) }}" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full flex items-center justify-center text-4xl bg-gradient-to-br from-indigo-500 to-blue-600 text-white font-black">
                            {{ strtoupper(substr($name, 0, 1)) }}
                        </div>
                    @endif
                    
                    <div wire:loading wire:target="avatar" class="absolute inset-0 bg-black/50 flex items-center justify-center">
                        <svg class="animate-spin h-8 w-8 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    </div>
                </div>
                <label for="avatar" class="absolute -bottom-2 -right-2 bg-indigo-600 hover:bg-indigo-700 text-white p-2.5 rounded-2xl shadow-lg cursor-pointer transition-transform hover:scale-110 active:scale-95 border-2 border-white dark:border-gray-800">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                </label>
                <input type="file" wire:model="avatar" id="avatar" class="hidden" accept="image/*">
            </div>
            <div class="flex-1 space-y-1 text-center sm:text-left">
                <p class="text-sm font-bold text-gray-700 dark:text-gray-300">Foto Profil</p>
                <p class="text-xs text-gray-400">JPG, PNG, atau GIF (Maks. 2MB)</p>
                <x-input-error class="mt-2" :messages="$errors->get('avatar')" />
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Name -->
            <div class="space-y-2">
                <x-input-label for="name" :value="__('Nama Lengkap')" class="font-bold text-gray-700 dark:text-gray-300" />
                <x-text-input wire:model="name" id="name" name="name" type="text" class="mt-1 block w-full !bg-gray-50 !border-gray-200 !text-gray-900 focus:!ring-indigo-500 focus:!border-indigo-500 rounded-2xl" required autofocus autocomplete="name" placeholder="Masukkan nama lengkap" />
                <x-input-error class="mt-2" :messages="$errors->get('name')" />
            </div>

            <!-- Email -->
            <div class="space-y-2">
                <x-input-label for="email" :value="__('Email')" class="font-bold text-gray-700 dark:text-gray-300" />
                <x-text-input wire:model="email" id="email" name="email" type="email" class="mt-1 block w-full !bg-gray-50 !border-gray-200 !text-gray-900 focus:!ring-indigo-500 focus:!border-indigo-500 rounded-2xl" required autocomplete="username" placeholder="name@example.com" />
                <x-input-error class="mt-2" :messages="$errors->get('email')" />

                @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! auth()->user()->hasVerifiedEmail())
                    <div class="mt-2 p-4 bg-amber-50 rounded-2xl border border-amber-100">
                        <p class="text-xs text-amber-700 leading-relaxed">
                            {{ __('Email Anda belum terverifikasi.') }}
                            <button wire:click.prevent="sendVerification" class="font-black underline hover:text-amber-800">
                                {{ __('Kirim ulang link verifikasi?') }}
                            </button>
                        </p>

                        @if (session('status') === 'verification-link-sent')
                            <p class="mt-2 font-bold text-xs text-green-600">
                                {{ __('Link verifikasi baru telah dikirim ke email Anda.') }}
                            </p>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        <div class="flex items-center gap-4 pt-4">
            <x-primary-button class="w-full sm:w-auto !px-10 !py-3.5 !rounded-2xl !bg-indigo-600 hover:!bg-indigo-700 shadow-lg shadow-indigo-200 transition-all active:scale-95">
                {{ __('Simpan Perubahan') }}
            </x-primary-button>

            <x-action-message class="text-emerald-600 font-bold" on="profile-updated">
                {{ __('Tersimpan!') }}
            </x-action-message>
        </div>
    </form>
</section>
