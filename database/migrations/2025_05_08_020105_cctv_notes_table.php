<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('cctv_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cctv_id')->constrained('cctvs')->onDelete('cascade');
            $table->string('notes', 255);
            $table->dateTime('created_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
        });
    }

    public function down(): void {
        Schema::dropIfExists('cctv_notes');
    }
};
