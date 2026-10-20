<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Pharmacists can review prescriptions without full admin rights, so `role`
        // becomes a plain string (customer | pharmacist | admin | super_admin).
        // Existing values are untouched; the app validates the allowed roles.
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 20)->default('customer')->change();
        });

        Schema::create('prescriptions', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('file_path');            // private disk, never public
            $table->string('original_name');
            $table->string('mime_type', 100);
            $table->unsignedInteger('file_size');
            $table->string('doctor_name')->nullable();
            $table->date('prescribed_on')->nullable();
            $table->string('status', 20)->default('pending'); // pending|verified|rejected|fulfilled|expired
            $table->string('extraction_status', 20)->default('manual'); // manual | suggested
            $table->text('staff_notes')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            $table->index(['customer_id', 'status']);
        });

        Schema::create('prescription_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prescription_id')->constrained()->cascadeOnDelete();
            $table->string('written_name');          // exactly as written / extracted
            $table->string('written_strength')->nullable();
            $table->string('written_form')->nullable();
            $table->unsignedInteger('prescribed_qty')->nullable();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('approved_qty')->default(0);
            $table->unsignedInteger('fulfilled_qty')->default(0);
            $table->boolean('is_verified')->default(false);
            $table->timestamps();
        });

        Schema::create('prescription_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prescription_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action', 60);
            $table->text('detail')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('change');               // + restock, - sale/adjustment
            $table->integer('balance');
            $table->string('reason', 40);            // sale, restock, adjustment, cancellation
            $table->string('reference')->nullable(); // order number etc.
            $table->string('note')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['product_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('prescription_logs');
        Schema::dropIfExists('prescription_items');
        Schema::dropIfExists('prescriptions');
    }
};
