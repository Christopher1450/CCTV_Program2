<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('cctvs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('branch_id')->constrained('branches');
            $table->foreignId('cctv_position_id')->constrained('cctv_positions');

            $table->string('name', 60)->unique();
            $table->boolean('is_active')->default(true);

            $table->tinyInteger('connection_status')->default(1); // 0 = Disconnected, 1 = Connected
            $table->tinyInteger('playback_status')->default(1);   // 0 = Error, 1 = Normal
            $table->tinyInteger('replacement_status')->default(1); // 0 = Need Replace, 1 = Normal

            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('cctvs');
    }
};
