@php
    use App\Models\Payment;
    use App\Models\ServiceJob;

    $viewer = auth()->user();
    $isOwner = $viewer->id === $job->user_id;
    $isWorker = $viewer->hasRole('worker');
    $hasApplied = filled($existingApplication ?? null);
    $clientRating = $job->rating;
    $workerRating = $job->workerRating;
    $workerNeedsPayoutDetails =
        $isWorker &&
        !$isOwner &&
        (!filled($viewer->bank_name) || !filled($viewer->account_name) || !filled($viewer->account_number));
    $workerNeedsVerification = $isWorker && (!$viewer->is_verified || !filled($viewer->id_document));

    $canApply =
        $isWorker &&
        !$isOwner &&
        !$hasApplied &&
        !$workerNeedsPayoutDetails &&
        !$workerNeedsVerification &&
        $job->status === 'open' &&
        $job->assigned_to === null &&
        $viewer->availability_status === 'available';

    $isAssignedWorker = (int) $job->assigned_to === (int) $viewer->id;
    $canSeeAssignedWorker = $job->worker && ($isOwner || $isAssignedWorker);
    $canChatOnJob =
        $job->worker &&
        in_array($job->status, ServiceJob::chatEligibleStatuses(), true) &&
        ($isOwner || $isAssignedWorker);
    $chatPartnerLabel = $isOwner ? Str::before($job->worker?->name, ' ') : Str::before($job->client?->name, ' ');
    $chatPartnerName = $isOwner ? $job->worker?->name : $job->client?->name;
    $canAcceptJob = $isAssignedWorker && $job->status === 'assigned';
    $canRejectJob = $isAssignedWorker && $job->status === 'assigned';
    $canStartJob = $isAssignedWorker && $job->status === 'worker_accepted';
    $canCompleteJob = $isAssignedWorker && $job->status === 'in_progress';
    $canMarkPaid = $isOwner && $job->status === 'payment_pending' && $job->paid_at === null;
    $canConfirmPayment = $isAssignedWorker && $job->status === 'payment_pending' && $job->paid_at !== null;
    $canRateWorker = $isOwner && in_array($job->status, ['completed', 'rated']) && !$clientRating && $job->worker;
    $canRateClient =
        $isAssignedWorker && in_array($job->status, ['completed', 'rated']) && !$workerRating && $job->client;
    $canViewTransferDetails =
        $isOwner && $job->worker && $job->status === 'payment_pending' && $job->payment_method === 'transfer';
    $showCashPaymentNote =
        $isOwner && $job->worker && $job->status === 'payment_pending' && $job->payment_method === 'cash';
    $acceptedStages = ['worker_accepted', 'in_progress', 'payment_pending', 'completed', 'rated'];
    $inProgressStages = ['in_progress', 'payment_pending', 'completed', 'rated'];
    $paymentPendingStages = ['payment_pending', 'completed', 'rated'];
    $closedStages = ['completed', 'rated'];

    $visibleNegotiations = $visibleNegotiations ?? collect();
    $latest = $latestNegotiation ?? $visibleNegotiations->first();
    $myNegotiations = $visibleNegotiations;
    $latestMine = $latestMine ?? $myNegotiations->first();
    $viewerNegotiationCount = $isOwner ? $visibleNegotiations->count() : $myNegotiations->count();
    $showNegotiationSection = $isOwner
        ? $job->status === 'open' || $visibleNegotiations->isNotEmpty()
        : $job->status === 'open' || $viewerNegotiationCount > 0;
    $agreedAmount = $job->agreed_amount ?? null;
    $paymentLifecycleStatus = match (true) {
        $job->status !== 'payment_pending' => $job->payment?->status,
        $job->paid_at === null => Payment::STATUS_AWAITING_CONFIRMATION,
        default => $job->payment?->status ?? Payment::STATUS_PENDING,
    };
    $paymentLifecycleLabel = $paymentLifecycleStatus ? str_replace('_', ' ', $paymentLifecycleStatus) : null;
@endphp
