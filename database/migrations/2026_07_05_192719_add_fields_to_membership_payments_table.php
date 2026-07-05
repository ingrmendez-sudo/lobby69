<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('membership_payments', function (Blueprint $table) {
            $table->string('user_id', 100);
            $table->string('requested_membership', 50);
            $table->string('current_membership', 50)->nullable();
            $table->decimal('amount', 10, 2)->nullable();
            $table->string('currency', 10)->default('MXN');
            $table->string('payment_method', 50)->nullable();
            $table->string('payment_reference', 200)->nullable();
            $table->string('receipt_path')->nullable();
            $table->string('status', 30)->default('pending');
            $table->text('admin_note')->nullable();
            $table->string('reviewed_by', 100)->nullable();
            $table->timestamp('reviewed_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('membership_payments', function (Blueprint $table) {
            $table->dropColumn([
                'user_id', 'requested_membership', 'current_membership',
                'amount', 'currency', 'payment_method', 'payment_reference',
                'receipt_path', 'status', 'admin_note', 'reviewed_by', 'reviewed_at'
            ]);
        });
    }
};
