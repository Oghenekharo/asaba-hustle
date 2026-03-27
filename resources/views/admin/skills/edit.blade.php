@extends('admin.layout')

@section('title', 'Edit Skill')
@section('admin-page-title', 'Edit Skill')

@section('content')
    <section
        class="mx-auto max-w-4xl relative overflow-hidden rounded-[3rem] border border-white bg-white/70 p-8 shadow-sm backdrop-blur-xl sm:p-10 transition-all">
        <!-- Brand Decorative Accent -->
        <div class="absolute -right-20 -top-24 h-64 w-64 rounded-full bg-[var(--brand)]/5 blur-3xl opacity-50"></div>
        <div class="absolute left-0 top-0 h-full w-1.5 rounded-r-full bg-[var(--brand)]"></div>

        <!-- Header Section -->
        <div
            class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between border-b border-[var(--brand)]/5 pb-8">
            <div class="flex items-center gap-5">
                <div
                    class="h-16 w-16 rounded-[1.5rem] bg-[var(--ink)] text-white flex items-center justify-center shadow-2xl shadow-slate-900/20 rotate-3">
                    <i data-lucide="{{ $skill->icon ?: 'sparkles' }}" class="h-8 w-8 text-orange-400"></i>
                </div>
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.3em] text-[var(--brand)]">Registry Update</p>
                    <h2 class="text-3xl font-black tracking-tighter text-[var(--ink)] sm:text-4xl">{{ $skill->name }}</h2>
                    <p class="mt-1 text-xs font-medium text-slate-500 italic">Editing global marketplace capability</p>
                </div>
            </div>

            <a href="{{ route('admin.skills.index') }}"
                class="inline-flex h-12 items-center gap-3 rounded-2xl border-2 border-slate-100 bg-white px-6 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 transition hover:border-[var(--brand)]/20 hover:text-[var(--brand)] shadow-sm">
                <i data-lucide="arrow-left" class="h-4 w-4"></i>
                Back to Library
            </a>
        </div>

        <!-- Form Section -->
        <form method="POST" action="{{ route('admin.skills.update', $skill) }}"
            class="mt-10 grid lg:grid-cols-[1fr_0.4fr] gap-10">
            @csrf
            @method('PATCH')

            <div class="space-y-6">
                <div class="grid gap-6 md:grid-cols-2">
                    <x-input name="name" label="Skill Label" icon="tag" placeholder="e.g. Electrician"
                        value="{{ old('name', $skill->name) }}" />

                    <x-input name="icon" label="Lucide Icon Key" icon="zap" placeholder="bolt, hammer, etc."
                        value="{{ old('icon', $skill->icon) }}" />
                </div>

                <x-input type="textarea" name="description" label="Operational Description" icon="align-left" rows="5"
                    placeholder="Describe what services this skill covers..."
                    value="{{ old('description', $skill->description) }}" class="!rounded-[2rem]" />

                <div class="flex flex-wrap items-center gap-4 pt-4">
                    <button type="submit"
                        class="flex items-center gap-3 rounded-[1.2rem] bg-[var(--ink)] px-8 py-4 text-[10px] font-black uppercase tracking-[0.24em] text-white shadow-xl shadow-slate-900/20 hover:bg-[var(--brand)] transition-all active:scale-95 group">
                        <i data-lucide="save" class="h-4 w-4 opacity-50 group-hover:opacity-100 transition-opacity"></i>
                        Commit Changes
                    </button>

                    <a href="{{ route('admin.skills.index') }}"
                        class="text-[10px] font-black uppercase tracking-[0.24em] text-slate-400 hover:text-rose-500 transition-colors px-4">
                        Discard Edits
                    </a>
                </div>
            </div>

            <!-- Sidebar Preview Card -->
            <aside class="hidden lg:block space-y-6">
                <div
                    class="sticky top-10 p-6 rounded-[2.5rem] bg-[var(--surface-soft)] border border-[var(--brand)]/10 text-center">
                    <p class="text-[9px] font-black uppercase tracking-widest text-slate-400 mb-6">Live Preview</p>

                    <div
                        class="inline-flex h-16 w-16 items-center justify-center rounded-2xl bg-white shadow-lg text-[var(--brand)] mb-4">
                        <i data-lucide="{{ $skill->icon ?: 'sparkles' }}" class="h-8 w-8"></i>
                    </div>

                    <h4 class="text-lg font-black tracking-tight text-[var(--ink)]">{{ $skill->name }}</h4>
                    <p class="mt-2 text-[10px] font-bold text-slate-400 line-clamp-3 px-2 italic">
                        "{{ $skill->description ?: 'Preview of the skill description...' }}"
                    </p>
                </div>
            </aside>
        </form>
    </section>

@endsection
