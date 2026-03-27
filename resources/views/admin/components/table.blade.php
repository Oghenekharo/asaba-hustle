@props([
    'headers' => [],
    'caption' => null,
])

<div class="overflow-hidden rounded-[2rem] border border-slate-200/80 bg-white/85 shadow-sm backdrop-blur-xl">
    @if ($caption)
        <div class="border-b border-slate-100 px-5 py-4">
            <p class="text-sm font-black text-slate-900">{{ $caption }}</p>
        </div>
    @endif

    <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50/90">
                <tr>
                    @foreach ($headers as $header)
                        <th class="whitespace-nowrap px-5 py-4 text-left text-[11px] font-black uppercase tracking-[0.24em] text-slate-500">
                            {{ $header }}
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                {{ $slot }}
            </tbody>
        </table>
    </div>
</div>
