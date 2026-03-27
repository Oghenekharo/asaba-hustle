<?php

use App\Models\Payment;
use App\Models\ServiceJob;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->enum('status', [
                Payment::STATUS_AWAITING_CONFIRMATION,
                Payment::STATUS_PENDING,
                Payment::STATUS_SUCCESSFUL,
                Payment::STATUS_FAILED,
                Payment::STATUS_REFUNDED,
            ])->default(Payment::STATUS_PENDING)->change();
        });

        DB::table('payments')
            ->join('service_jobs', 'service_jobs.id', '=', 'payments.job_id')
            ->where('service_jobs.status', ServiceJob::STATUS_PAYMENT_PENDING)
            ->whereNull('service_jobs.paid_at')
            ->where('payments.status', Payment::STATUS_PENDING)
            ->update([
                'payments.status' => Payment::STATUS_AWAITING_CONFIRMATION,
                'payments.verified_at' => null,
            ]);
    }

    public function down(): void
    {
        DB::table('payments')
            ->where('status', Payment::STATUS_AWAITING_CONFIRMATION)
            ->update([
                'status' => Payment::STATUS_PENDING,
            ]);

        Schema::table('payments', function (Blueprint $table) {
            $table->enum('status', [
                Payment::STATUS_PENDING,
                Payment::STATUS_SUCCESSFUL,
                Payment::STATUS_FAILED,
                Payment::STATUS_REFUNDED,
            ])->default(Payment::STATUS_PENDING)->change();
        });
    }
};
