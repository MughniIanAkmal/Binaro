@props(['type' => 'info'])

@php
    $styles = [
        'success' => ['bg' => 'bg-success-tint', 'border' => 'border-success/30', 'text' => 'text-emerald-800', 'icon' => 'fa-circle-check', 'iconColor' => 'text-success', 'close' => 'text-success/70 hover:text-success'],
        'error'   => ['bg' => 'bg-danger-tint', 'border' => 'border-danger/30', 'text' => 'text-rose-800', 'icon' => 'fa-circle-exclamation', 'iconColor' => 'text-danger', 'close' => 'text-danger/70 hover:text-danger'],
        'info'    => ['bg' => 'bg-info-tint', 'border' => 'border-info/30', 'text' => 'text-sky-800', 'icon' => 'fa-circle-info', 'iconColor' => 'text-info', 'close' => 'text-info/70 hover:text-info'],
    ];
    $s = $styles[$type] ?? $styles['info'];
@endphp

<div {{ $attributes->merge(['class' => "x-alert p-3.5 rounded-xl {$s['bg']} border {$s['border']} {$s['text']} text-xs flex items-center justify-between shadow-sm"]) }}>
    <div class="flex items-center gap-2.5">
        <i class="fas {{ $s['icon'] }} {{ $s['iconColor'] }} text-base"></i>
        <span class="font-semibold">{{ $slot }}</span>
    </div>
    <button type="button" onclick="this.closest('.x-alert').remove()" class="{{ $s['close'] }} shrink-0">
        <i class="fas fa-xmark"></i>
    </button>
</div>
