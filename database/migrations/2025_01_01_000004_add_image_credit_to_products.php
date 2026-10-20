<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Attribution for photos taken from open-licensed sources (e.g. CC BY-SA).
            $table->string('image_credit')->nullable()->after('images');
            $table->string('image_credit_url', 500)->nullable()->after('image_credit');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['image_credit', 'image_credit_url']);
        });
    }
};
