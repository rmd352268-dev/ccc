<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cards', function (Blueprint $table) {
            $table->id();
            $table->string('bin', 10)->index();
            $table->string('brand', 30)->index();
            $table->string('type', 20)->index(); // DEBIT, CREDIT
            $table->string('country_code', 5)->index(); // GB, FR, BE, US, etc.
            $table->string('country_name', 100);
            $table->boolean('has_name')->default(true);
            $table->boolean('has_address')->default(true);
            $table->boolean('has_zip')->default(true);
            $table->boolean('has_phone')->default(true);
            $table->boolean('has_mail')->default(false);
            $table->boolean('has_ssn')->default(false);
            $table->boolean('has_dob')->default(false);
            $table->boolean('has_user_agent')->default(false);
            $table->boolean('has_email_password')->default(false);
            $table->string('bank', 150)->index();
            $table->string('base_name', 150)->index();
            $table->boolean('refundable')->default(true);
            $table->decimal('price_c', 8, 2)->default(2.00);
            $table->decimal('price_unc', 8, 2)->default(1.50);
            $table->string('card_number', 25);
            $table->string('exp_date', 10);
            $table->string('cvv', 6);
            $table->string('holder_name')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('zip', 20)->nullable()->index();
            $table->string('phone', 30)->nullable();
            $table->string('email', 100)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('email_password')->nullable();
            $table->string('status', 20)->default('available')->index(); // available, sold
            $table->timestamps();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number', 30)->unique();
            $table->decimal('total_amount', 10, 2);
            $table->integer('item_count')->default(1);
            $table->string('status', 20)->default('completed');
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('card_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('price', 8, 2);
            $table->json('card_details')->nullable();
            $table->timestamps();
        });

        Schema::create('deposits', function (Blueprint $table) {
            $table->id();
            $table->string('trx_id', 50)->unique();
            $table->string('currency', 20); // USDT TRC20, BTC, LTC, PM
            $table->decimal('amount', 10, 2);
            $table->string('address', 100);
            $table->string('status', 20)->default('completed');
            $table->timestamps();
        });

        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number', 20)->unique();
            $table->string('subject', 200);
            $table->string('department', 50)->default('General');
            $table->string('priority', 20)->default('Medium'); // Low, Medium, High
            $table->string('status', 20)->default('Open'); // Open, Answered, Closed
            $table->timestamps();
        });

        Schema::create('ticket_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained()->cascadeOnDelete();
            $table->string('sender', 20)->default('user'); // user, support
            $table->text('message');
            $table->timestamps();
        });

        Schema::create('news', function (Blueprint $table) {
            $table->id();
            $table->string('title', 200);
            $table->string('category', 50)->default('Update');
            $table->text('content');
            $table->boolean('is_pinned')->default(false);
            $table->timestamps();
        });

        Schema::create('wholesale_packs', function (Blueprint $table) {
            $table->id();
            $table->string('title', 150);
            $table->text('description')->nullable();
            $table->integer('card_count');
            $table->decimal('price', 10, 2);
            $table->decimal('original_price', 10, 2);
            $table->string('country', 50);
            $table->string('type', 30);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wholesale_packs');
        Schema::dropIfExists('news');
        Schema::dropIfExists('ticket_messages');
        Schema::dropIfExists('tickets');
        Schema::dropIfExists('deposits');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('cards');
    }
};
