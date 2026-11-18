@props(['icon' => 'fa-inbox', 'title' => '', 'message' => null])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center text-center py-10 px-4']) }}>
    <div class="w-11 h-11 rounded-xl bg-surface-alt text-ink-muted flex items-center justify-center text-base">
        <i class="fas {{ $icon }}"></i>
    </div>
    <p class="text-xs font-bold text-slate-700 mt-3">{{ $title }}</p>
    @if($message)
        <p class="text-[11px] text-slate-500 mt-1 max-w-xs leading-relaxed">{{ $message }}</p>
    @endif
    {{ $slot }}
</div>
