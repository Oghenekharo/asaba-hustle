<form method="POST" action="{{ route('web.logout') }}">
    @csrf
    <button type="submit"
        {{ $attributes->merge(['class' => 'group cursor-pointer flex items-center h-10 px-3 py-2 rounded-xl bg-slate-50 border border-slate-100 text-slate-400 hover:text-rose-600 hover:bg-rose-50 hover:border-rose-100 transition-all duration-500 ease-out overflow-hidden']) }}>

        <!-- The Text: Starts collapsed -->
        <span
            class="max-w-0 overflow-hidden whitespace-nowrap text-[10px] font-black uppercase tracking-widest opacity-0 group-hover:max-w-[80px] group-hover:opacity-100 group-hover:mr-2 transition-all duration-500 ease-out">
            Logout
        </span>

        <!-- The Icon: Stays static -->
        <i data-lucide="log-out" class="w-4 h-4 shrink-0"></i>
    </button>
</form>
