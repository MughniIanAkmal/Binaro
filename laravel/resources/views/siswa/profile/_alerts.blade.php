@if(session('success'))
<div class="p-3 bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs rounded-xl flex items-center gap-2">
    <i class="fas fa-circle-check text-emerald-500 shrink-0"></i>
    <span>{{ session('success') }}</span>
</div>
@endif
@if(session('error'))
<div class="p-3 bg-rose-50 border border-rose-200 text-rose-700 text-xs rounded-xl flex items-center gap-2">
    <i class="fas fa-circle-exclamation text-rose-500 shrink-0"></i>
    <span>{{ session('error') }}</span>
</div>
@endif
@if($errors->any())
<div class="p-3 bg-rose-50 border border-rose-200 text-rose-700 text-xs rounded-xl">
    <ul class="list-disc list-inside space-y-0.5">
        @foreach($errors->all() as $err)
        <li>{{ $err }}</li>
        @endforeach
    </ul>
</div>
@endif