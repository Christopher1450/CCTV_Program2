<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('work_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId(column: 'cctv_id')->constrained('cctvs')->onDelete('cascade');
            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
            $table->unsignedTinyInteger('problem_type'); // 1: Connection 2: Playback
            $table->unsignedTinyInteger('status'); // 1: Pending, 2: On Progress, 3: Done, 4: Waiting Replacement
            $table->string('notes', 100);
            $table->foreignId('taken_by')->nullable()->constrained('users')->onDelete('set null'); //id
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('work_orders');
    }
};

