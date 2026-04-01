@if ($canRateWorker || $canRateClient)
    <section class="rounded-[2rem] bg-white border border-slate-100 shadow-sm p-6">
        <p class="text-[10px] font-black uppercase text-slate-400 mb-4">
            {{ $canRateWorker ? 'Rate Worker' : 'Rate Client' }}
        </p>
        <div class="rounded-2xl border border-violet-100 bg-violet-50 px-4 py-4">
            <p class="text-xs font-black uppercase text-violet-700">
                {{ $canRateWorker ? 'Rate the worker now that payment is confirmed.' : 'Rate the client now that payment is confirmed.' }}
            </p>
            <p class="mt-2 text-sm font-medium text-violet-900">
                Your feedback helps both sides build trust on the platform.
            </p>
            <button type="button" onclick="openModal('jobRatingModal')"
                class="mt-4 inline-flex items-center gap-2 rounded-2xl bg-slate-900 px-4 py-3 text-[10px] font-black uppercase tracking-widest text-white transition hover:opacity-90">
                <i data-lucide="star" class="h-4 w-4"></i>
                Open Rating Form
            </button>
        </div>
    </section>
@elseif($isOwner && $clientRating)
    <section class="rounded-[2rem] bg-white border border-slate-100 shadow-sm p-6">
        <p class="text-[10px] font-black uppercase text-slate-400 mb-4">You Rated Worker</p>
        <div class="rounded-2xl border border-violet-100 bg-violet-50 px-4 py-4">
            <div class="flex items-center gap-2 text-violet-700">
                <i data-lucide="star" class="h-4 w-4 fill-current"></i>
                <p class="text-sm font-black">{{ number_format($clientRating->rating, 1) }}/5.0</p>
            </div>
            <div class="flex mt-3 items-center justify-start gap-2">
                <div>
                    <i data-lucide="user-circle" class="h-6 w-6 text-violet-900"></i>
                </div>
                <div>
                    <p class="text-sm font-bold text-violet-900">
                        {{ $clientRating->worker?->name ?: 'Anonymous.' }}
                    </p>
                    <p class="text-xs font-normal text-violet-900">
                        {{ $clientRating->review ?: 'You rated this worker after the job was closed.' }}
                    </p>
                </div>
            </div>
        </div>
    </section>
@elseif($isAssignedWorker && $workerRating)
    <section class="rounded-[2rem] bg-white border border-slate-100 shadow-sm p-6">
        <p class="text-[10px] font-black uppercase text-slate-400 mb-4">You Rated Client</p>
        <div class="rounded-2xl border border-violet-100 bg-violet-50 px-4 py-4">
            <div class="flex items-center gap-2 text-violet-700">
                <i data-lucide="star" class="h-4 w-4 fill-current"></i>
                <p class="text-sm font-black">{{ number_format($workerRating->rating, 1) }}/5.0</p>
            </div>
            <div class="flex mt-3 items-center justify-start gap-2">
                <div>
                    <i data-lucide="user-circle" class="h-6 w-6 text-violet-900"></i>
                </div>
                <div>
                    <p class="text-sm font-bold text-violet-900">
                        {{ $workerRating->client?->name ?: 'Anonymous.' }}
                    </p>
                    <p class="text-xs font-normal text-violet-900">
                        {{ $workerRating->review ?: 'You rated this client after the job was closed.' }}
                    </p>
                </div>
            </div>
        </div>
    </section>
@endif

@if ($canRateWorker || $canRateClient)
    <x-modal id="jobRatingModal" title="{{ $canRateWorker ? 'Rate Worker' : 'Rate Client' }}" size="max-w-xl">
        <form id="job-rate-form" action="{{ route('web.app.jobs.rate', $job) }}" method="POST" class="space-y-4">
            @csrf
            <div class="rounded-[1.75rem] border border-violet-100 bg-violet-50/70 px-5 py-4">
                <p class="text-[10px] font-black uppercase tracking-widest text-violet-700">
                    {{ $canRateWorker ? 'Worker Review' : 'Client Review' }}
                </p>
                <p class="mt-2 text-sm font-medium text-violet-900">
                    {{ $canRateWorker ? 'Payment has been confirmed. Rate the worker before you leave this job.' : 'Payment has been confirmed. Rate the client before you leave this job.' }}
                </p>
            </div>

            <x-input type="number" step="0.1" name="rating" id="job_worker_rating" icon="star" label="Star Rating"
                placeholder="Give a score from 1.0 to 5.0" required />

            <x-input type="textarea" name="review" id="job_worker_review" rows="4" icon="message-square-quote"
                label="Review"
                placeholder="{{ $canRateWorker ? 'Share how the worker handled this job...' : 'Share how the client handled this job...' }}" />
            <x-error />
            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <x-button type="button" color="black" variant="outline" onclick="closeModal('jobRatingModal')">
                    <x-slot:icon>
                        <i data-lucide="x" class="h-4 w-4"></i>
                    </x-slot:icon>
                    Later
                </x-button>
                <x-button id="job-rate-submit" type="submit">
                    <x-slot:icon>
                        <i data-lucide="star" class="h-4 w-4"></i>
                    </x-slot:icon>
                    Submit Rating
                </x-button>
            </div>
        </form>
    </x-modal>
@endif
