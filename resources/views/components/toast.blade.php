@php
    $type = null;
    $message = null;

    if (session()->has('success')) {
        $type = 'success';
        $message = session('success');
    } elseif (session()->has('error')) {
        $type = 'error';
        $message = session('error');
    } elseif (session()->has('warning')) {
        $type = 'warning';
        $message = session('warning');
    } elseif (session()->has('status') && session('status') === 'profile-updated') {
        $type = 'success';
        $message = 'Profil berhasil diperbarui.';
    } elseif (session()->has('status') && session('status') === 'password-updated') {
        $type = 'success';
        $message = 'Kata sandi berhasil diperbarui.';
    }

    $configs = [
        'success' => [
            'border' => 'border-l-4 border-l-success',
            'icon' => 'check-circle',
            'iconColor' => 'text-success',
        ],
        'error' => [
            'border' => 'border-l-4 border-l-danger',
            'icon' => 'x-circle',
            'iconColor' => 'text-danger',
        ],
        'warning' => [
            'border' => 'border-l-4 border-l-warning',
            'icon' => 'exclamation-triangle',
            'iconColor' => 'text-warning',
        ],
    ];
@endphp

@if ($type && $message)
    @php($cfg = $configs[$type])
    <div
        x-data="{ show: true }"
        x-init="setTimeout(() => show = false, 5000)"
        x-show="show"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-2"
        role="alert"
        class="fixed top-5 right-5 z-50 w-80 bg-white rounded-xl shadow-pop border border-line {{ $cfg['border'] }} p-4 flex items-start gap-3"
    >
        <x-icon :name="$cfg['icon']" class="w-5 h-5 {{ $cfg['iconColor'] }} mt-0.5 shrink-0" />
        <div class="flex-1 text-sm text-ink font-medium">
            {{ $message }}
        </div>
        <button
            type="button"
            @click="show = false"
            aria-label="Tutup notifikasi"
            class="text-subtle hover:text-ink rounded-lg p-1 transition-colors duration-150"
        >
            <x-icon name="x-mark" class="w-4 h-4" />
        </button>
    </div>
@endif

