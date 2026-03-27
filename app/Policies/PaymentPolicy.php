<?php

namespace App\Policies;

use App\Models\Payment;
use App\Models\ServiceJob;
use App\Models\User;

class PaymentPolicy
{
    public function initialize(User $user, ServiceJob $job): bool
    {
        return $user->id === $job->user_id;
    }

    public function verify(User $user, Payment $payment): bool
    {
        return $user->id === $payment->user_id;
    }
}
