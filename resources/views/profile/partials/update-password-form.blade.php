<section>
    <header>
        <h2 class="text-lg font-semibold text-ink">Ubah Kata Sandi</h2>
        <p class="mt-1 text-sm text-subtle">
            Pastikan akun Anda menggunakan kata sandi minimal 8 karakter agar tetap aman.
        </p>
    </header>

    <form
        method="post"
        action="{{ route('password.update') }}"
        x-data="{ submitting: false }"
        @submit="submitting = true"
        class="mt-5 space-y-4"
    >
        @csrf
        @method('put')

        <div>
            <x-label for="update_password_current_password" value="Kata Sandi Saat Ini" :required="true" />
            <x-input id="update_password_current_password" name="current_password" type="password" autocomplete="current-password" />
            <x-input-error :messages="$errors->updatePassword->get('current_password')" />
        </div>

        <div>
            <x-label for="update_password_password" value="Kata Sandi Baru" :required="true" />
            <x-input id="update_password_password" name="password" type="password" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password')" />
        </div>

        <div>
            <x-label for="update_password_password_confirmation" value="Konfirmasi Kata Sandi Baru" :required="true" />
            <x-input id="update_password_password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" />
        </div>

        <div class="flex items-center gap-4 pt-2">
            <x-button type="submit" variant="primary" ::disabled="submitting">
                <span x-text="submitting ? 'Menyimpan…' : 'Perbarui Kata Sandi'">Perbarui Kata Sandi</span>
            </x-button>

            @if (session('status') === 'password-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-success-ink font-medium"
                >Tersimpan.</p>
            @endif
        </div>
    </form>
</section>
