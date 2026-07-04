<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('membership_payments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('user_id');
            $table->string('requested_membership');
            $table->string('payment_method')->nullable();
            $table->decimal('amount', 10, 2)->nullable();
            $table->string('reference')->nullable();
            $table->string('receipt_path')->nullable();
            $table->string('status')->default('pending');
            $table->text('admin_note')->nullable();
            $table->string('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('membership_payments');
    }
};
