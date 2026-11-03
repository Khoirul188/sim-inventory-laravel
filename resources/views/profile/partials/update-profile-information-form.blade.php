<section>
    <header>
        <h2 class="text-lg font-semibold text-ink">Informasi Profil</h2>
        <p class="mt-1 text-sm text-subtle">
            Perbarui nama dan alamat email akun Anda.
        </p>
    </header>

    <form
        method="post"
        action="{{ route('profile.update') }}"
        x-data="{ submitting: false }"
        @submit="submitting = true"
        class="mt-5 space-y-4"
    >
        @csrf
        @method('patch')

        <x-input
            id="name"
            name="name"
            type="text"
            label="Nama Lengkap"
            :value="old('name', $user->name)"
            required
            autofocus
            autocomplete="name"
        />

        <x-input
            id="email"
            name="email"
            type="email"
            label="Email"
            :value="old('email', $user->email)"
            required
            autocomplete="username"
        />

        <div class="flex items-center gap-4 pt-2">
            <x-button type="submit" variant="primary" ::disabled="submitting">
                <span x-text="submitting ? 'Menyimpan…' : 'Simpan Profil'">Simpan Profil</span>
            </x-button>

            @if (session('status') === 'profile-updated')
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
