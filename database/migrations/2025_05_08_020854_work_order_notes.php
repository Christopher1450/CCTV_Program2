<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('work_order_notes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('work_order_id')->constrained('work_orders')->onDelete('cascade');
            $table->string('notes');

            $table->datetime('created_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
        });
    }

    public function down(): void {
        Schema::dropIfExists('work_order_notes');
    }
};

