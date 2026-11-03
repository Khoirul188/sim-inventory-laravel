@props([
    'name',
    'title' => 'Konfirmasi Tindakan',
    'message' => 'Apakah Anda yakin ingin melanjutkan tindakan ini?',
    'confirmText' => 'Hapus',
    'cancelText' => 'Batal',
    'action' => '#',
    'method' => 'DELETE',
])

<div
    x-data="{ open: false }"
    x-on:open-confirm-{{ $name }}.window="open = true"
    x-on:keydown.escape.window="open = false"
    x-show="open"
    style="display: none;"
    class="fixed inset-0 z-50 flex items-center justify-center p-4"
    role="dialog"
    aria-modal="true"
>
    <div
        x-show="open"
        x-transition.opacity.duration.150ms
        @click="open = false"
        class="fixed inset-0 bg-ink/50"
    ></div>

    <div
        x-show="open"
        x-transition.duration.150ms
        class="relative bg-white rounded-2xl shadow-pop border border-line/60 p-6 w-full max-w-md z-10"
    >
        <div class="flex items-start gap-4">
            <div class="w-10 h-10 rounded-full bg-danger-soft text-danger grid place-items-center shrink-0">
                <x-icon name="exclamation-triangle" class="w-5 h-5" />
            </div>
            <div class="flex-1">
                <h3 class="text-lg font-semibold text-ink">{{ $title }}</h3>
                <p class="text-sm text-muted mt-1">{{ $message }}</p>
            </div>
        </div>

        <form method="POST" action="{{ $action }}" class="mt-6 flex items-center justify-end gap-3" x-data="{ submitting: false }" @submit="submitting = true">
            @csrf
            @if (strtoupper($method) !== 'POST')
                @method($method)
            @endif
            <x-button type="button" variant="secondary" @click="open = false">
                {{ $cancelText }}
            </x-button>
            <x-button type="submit" variant="danger" ::disabled="submitting">
                <span x-text="submitting ? 'Memproses…' : '{{ $confirmText }}'">{{ $confirmText }}</span>
            </x-button>
        </form>
    </div>
</div>

