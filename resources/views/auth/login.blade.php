<x-guest-layout>
    <div class="mb-6">
        <h1 class="text-xl font-semibold text-ink">Masuk ke Akun Anda</h1>
        <p class="text-sm text-subtle mt-1">Masukkan email dan kata sandi untuk melanjutkan.</p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form
        method="POST"
        action="{{ route('login') }}"
        x-data="{ submitting: false, showPassword: false }"
        @submit="submitting = true"
        class="space-y-4"
    >
        @csrf

        <!-- Email -->
        <div>
            <x-label for="email" value="Email" :required="true" />
            <x-input
                id="email"
                type="email"
                name="email"
                :value="old('email')"
                placeholder="nama@inventory.test"
                required
                autofocus
                autocomplete="username"
            />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <!-- Kata Sandi dengan Ikon Mata -->
        <div>
            <x-label for="password" value="Kata Sandi" :required="true" />
            <div class="relative">
                <input
                    id="password"
                    :type="showPassword ? 'text' : 'password'"
                    name="password"
                    required
                    autocomplete="current-password"
                    placeholder="••••••••"
                    class="h-10 w-full rounded-lg border {{ $errors->has('password') ? 'border-danger focus:border-danger focus:ring-danger/20' : 'border-line focus:border-primary-600 focus:ring-primary-600/20' }} bg-white pl-3 pr-10 text-sm text-ink placeholder:text-subtle focus:ring-2 transition-colors duration-150"
                />
                <button
                    type="button"
                    @click="showPassword = !showPassword"
                    :aria-label="showPassword ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi'"
                    class="absolute right-2.5 top-1/2 -translate-y-1/2 text-subtle hover:text-ink p-1 rounded-md focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-600/40"
                >
                    <x-icon x-show="!showPassword" name="eye" class="w-4 h-4" />
                    <x-icon x-show="showPassword" style="display: none;" name="eye-slash" class="w-4 h-4" />
                </button>
            </div>
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <!-- Ingat Saya -->
        <div class="flex items-center justify-between pt-1">
            <label for="remember_me" class="inline-flex items-center cursor-pointer">
                <input
                    id="remember_me"
                    type="checkbox"
                    name="remember"
                    class="rounded border-line text-primary-600 focus:ring-primary-600/40"
                />
                <span class="ms-2 text-sm text-muted">Ingat saya</span>
            </label>
        </div>

        <!-- Tombol Masuk -->
        <div class="pt-2">
            <x-button type="submit" variant="primary" class="w-full" ::disabled="submitting">
                <span x-text="submitting ? 'Memproses…' : 'Masuk'">Masuk</span>
            </x-button>
        </div>
    </form>
</x-guest-layout>
