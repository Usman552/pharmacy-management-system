<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // ─── CATEGORIES ───
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->string('icon')->nullable()->default('📦');
            $table->string('color')->nullable()->default('#0d6efd');
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // ─── PRODUCTS ───
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('sku')->unique()->nullable();
            $table->string('brand')->nullable();
            $table->text('description')->nullable();
            $table->longText('details')->nullable();
            $table->string('image')->nullable();
            $table->json('images')->nullable(); // multiple images
            $table->decimal('price', 10, 2);
            $table->decimal('sale_price', 10, 2)->nullable();
            $table->integer('stock')->default(0);
            $table->integer('min_stock_alert')->default(10);
            $table->date('expiry_date')->nullable();
            $table->string('manufacturer')->nullable();
            $table->string('composition')->nullable(); // for medicines
            $table->string('dosage')->nullable();
            $table->string('weight')->nullable();
            $table->decimal('rating', 3, 2)->default(0);
            $table->integer('reviews_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_new')->default(false);
            $table->boolean('is_popular')->default(false);
            $table->boolean('is_sale')->default(false);
            $table->boolean('requires_prescription')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // ─── PRODUCT REVIEWS ───
        Schema::create('product_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('rating');
            $table->text('review')->nullable();
            $table->boolean('is_approved')->default(false);
            $table->timestamps();
        });

        // ─── BANNERS ───
        Schema::create('banners', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->string('button_text')->nullable();
            $table->string('button_link')->nullable();
            $table->string('badge_text')->nullable();
            $table->string('background_color')->nullable()->default('#edf4f6');
            $table->string('text_color')->nullable()->default('#184363');
            $table->enum('position', ['hero', 'promo', 'side'])->default('hero');
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_reviews');
        Schema::dropIfExists('products');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('banners');
    }
};
