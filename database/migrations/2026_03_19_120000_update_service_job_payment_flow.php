<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('service_jobs', function (Blueprint $table) {
            $table->timestamp('paid_at')->nullable()->after('assigned_to');
        });

        if (Schema::hasColumn('service_jobs', 'payment_method')) {
            Schema::table('service_jobs', function (Blueprint $table) {
                $table->string('payment_method')->default('cash')->change();
            });
        }

        DB::table('service_jobs')->where('payment_method', 'paystack')->update(['payment_method' => 'transfer']);
        DB::table('service_jobs')->where('payment_method', 'flutterwave')->update(['payment_method' => 'transfer']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('service_jobs')->where('payment_method', 'transfer')->update(['payment_method' => 'cash']);

        Schema::table('service_jobs', function (Blueprint $table) {
            $table->dropColumn('paid_at');
        });
    }
};
