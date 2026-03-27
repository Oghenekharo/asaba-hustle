@extends('admin.layout')

@section('title', 'Skills')
@section('admin-page-title', 'Skills')

@section('content')
    <section class="grid gap-6 xl:grid-cols-[0.95fr_1.05fr]">
        <div
            class="relative overflow-hidden rounded-[2.5rem] border border-white bg-white/70 p-6 shadow-sm backdrop-blur-xl transition-all hover:shadow-md">
            <!-- Brand Accent -->
            <div class="absolute left-0 top-0 h-full w-1 rounded-r-full bg-[var(--brand)] opacity-50"></div>

            <!-- Header: Compact & Contextual -->
            <div class="mb-6 flex items-center justify-between border-b border-[var(--brand)]/5 pb-4">
                <div class="flex items-center gap-3">
                    <div
                        class="flex h-9 w-9 items-center justify-center rounded-xl bg-[var(--brand)]/10 text-[var(--brand)]">
                        <i data-lucide="layers-3" class="h-5 w-5"></i>
                    </div>
                    <div>
                        <p class="text-[9px] font-black uppercase tracking-[0.3em] text-[var(--brand)]">Taxonomy</p>
                        <h2 class="text-lg font-black tracking-tighter text-[var(--ink)]">Add Marketplace Skill</h2>
                    </div>
                </div>
                <i data-lucide="info" class="h-4 w-4 text-slate-300 hover:text-[var(--brand)] cursor-help transition-colors"
                    title="Skills power search and worker categorization."></i>
            </div>

            <!-- Form: Slim & Structured -->
            <form method="POST" action="{{ route('admin.skills.store') }}" class="space-y-5">
                @csrf

                <x-input type="text" label="Skill Name" name="name" placeholder="e.g. Plumber"
                    icon="briefcase-business" />

                <x-input type="text" label="Lucide Icon" name="icon" placeholder="e.g. zap, hammer" icon="zap" />


                <x-input type="textarea" rows="4" label="Scope / Description" name="description"
                    placeholder="Briefly define what services this covers..." icon="scroll-text" />

                <!-- High-Contrast Action -->
                <button type="submit"
                    class="group flex w-full items-center justify-center gap-3 rounded-2xl bg-[var(--ink)] py-3.5 text-[10px] font-black uppercase tracking-[0.24em] text-white shadow-xl shadow-slate-900/10 transition-all hover:bg-[var(--brand)] active:scale-95">
                    <span>Create Skill</span>
                    <i data-lucide="plus-circle" class="h-4 w-4 transition-transform group-hover:rotate-90"></i>
                </button>
            </form>
        </div>


        <div
            class="rounded-[2.2rem] border border-slate-200/80 h-137.5 overflow-scroll bg-white/85 p-6 shadow-sm backdrop-blur-xl">
            <div class="flex flex-col gap-4">
                <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.28em] text-slate-400">Skill Library</p>
                    <h3 class="mt-1 text-xl font-black text-slate-950">Browse, edit, and remove skills</h3>
                </div>

                <form method="GET" action="{{ route('admin.skills.index') }}" class="flex gap-3">
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Search skills"
                        class="h-11 rounded-2xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 outline-none transition placeholder:text-slate-300 focus:border-[var(--brand)]">
                    <button
                        class="h-11 rounded-2xl border border-slate-200 px-4 text-xs font-black uppercase tracking-[0.24em] text-slate-500 transition hover:border-slate-300 hover:text-slate-900">
                        Search
                    </button>
                </form>
            </div>

            <div class="mt-6 space-y-4">
                @forelse ($skills as $skill)
                    <article
                        class="group relative overflow-hidden rounded-[2rem] border border-white bg-white p-6 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-black/5">
                        <!-- Brand Accent (Subtle) -->
                        <div
                            class="absolute left-0 top-0 h-full w-1 rounded-r-full bg-[var(--brand)] opacity-20 group-hover:opacity-100 transition-opacity">
                        </div>

                        <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
                            <div class="min-w-0 flex-1">
                                <!-- Header: Icon & Title -->
                                <div class="flex items-center gap-4">
                                    <div
                                        class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-[var(--ink)] text-white shadow-lg transition-transform group-hover:rotate-6">
                                        <i data-lucide="{{ $skill->icon ?: 'sparkles' }}"
                                            class="h-5 w-5 text-orange-400"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <h4
                                            class="truncate text-lg font-black tracking-tighter text-[var(--ink)] group-hover:text-[var(--brand)] transition-colors">
                                            {{ $skill->name }}
                                        </h4>
                                        <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-300">
                                            {{ $skill->icon ?: 'standard-icon' }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Description: Italic & Slim -->
                                <p class="mt-4 text-xs font-medium leading-relaxed text-slate-500 italic line-clamp-2">
                                    "{{ $skill->description ?: 'No operational description provided yet.' }}"
                                </p>

                                <!-- Metadata Pills -->
                                <div class="mt-5 flex flex-wrap gap-2">
                                    <div
                                        class="flex items-center gap-2 rounded-xl bg-[var(--surface-soft)] px-3 py-1.5 border border-[var(--brand)]/5">
                                        <i data-lucide="briefcase" class="h-3 w-3 text-blue-500"></i>
                                        <span
                                            class="text-[9px] font-black uppercase tracking-widest text-slate-500">{{ $jobCount ?? $skill->jobs_count }}
                                            Active Jobs</span>
                                    </div>
                                    <div
                                        class="flex items-center gap-2 rounded-xl bg-[var(--surface-soft)] px-3 py-1.5 border border-[var(--brand)]/5">
                                        <i data-lucide="users" class="h-3 w-3 text-orange-500"></i>
                                        <span
                                            class="text-[9px] font-black uppercase tracking-widest text-slate-500">{{ ($skill->primary_users_count ?? 0) + ($skill->users_count ?? 0) }}
                                            Providers</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Operations Bar -->
                            <div class="flex items-center gap-2 lg:flex-col lg:items-end">
                                <a href="{{ route('admin.skills.edit', $skill) }}"
                                    class="flex h-10 w-full min-w-[80px] items-center justify-center rounded-xl bg-[var(--ink)] px-4 text-[10px] font-black uppercase tracking-widest text-white transition-all hover:bg-[var(--brand)] shadow-sm active:scale-95">
                                    Edit
                                </a>
                                <form method="POST" action="{{ route('admin.skills.destroy', $skill) }}" class="w-full">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="flex h-10 w-full min-w-[80px] items-center justify-center rounded-xl bg-rose-50 px-4 text-[10px] font-black uppercase tracking-widest text-rose-600 transition-all hover:bg-rose-600 hover:text-white border border-rose-100 active:scale-95"
                                        onclick="return confirm('Archive this skill? This may affect worker categorization.')">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    </article>

                @empty
                    <x-empty-state title="No skills found" subtitle="Use the form to add a new skill" />
                @endforelse
            </div>

            <div class="mt-6">
                {{ $skills->withQueryString()->links('partials.pagination') }}
            </div>
        </div>
    </section>
@endsection
