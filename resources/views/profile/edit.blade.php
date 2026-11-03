<x-app-layout title="Profil Saya">
    <x-page-header
        title="Profil Saya"
        :breadcrumbs="['Pengaturan' => null, 'Profil Saya' => route('profile.edit')]"
    />

    <div class="space-y-6 max-w-3xl">
        <x-card>
            @include('profile.partials.update-profile-information-form')
        </x-card>

        <x-card>
            @include('profile.partials.update-password-form')
        </x-card>
    </div>
</x-app-layout>
