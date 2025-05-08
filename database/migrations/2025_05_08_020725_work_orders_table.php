<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('work_orders', function (Blueprint $table) {
            $table->id();

            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
            $table->foreignId('cctv_id')->constrained('cctvs')->onDelete('cascade');

            $table->string('title', 100);
            $table->text('description');
            $table->enum('status', ['open', 'in_progress', 'closed'])->default('open');

            $table->foreignId('taken_by')->nullable()->constrained('users')->onDelete('set null');

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('work_orders');
    }
};

