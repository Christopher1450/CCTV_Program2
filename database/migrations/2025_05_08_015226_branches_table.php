<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->string('name', length: 60)->unique();

            $table->foreignId('internet_provider_id')->constrained('internet_providers');
            $table->string('internet_customer_id', 60);

            $table->tinyInteger('cctv_type'); // 1 = DVR, 2 = IP Cam
            $table->foreignId('ip_cam_account_id')->constrained('ip_cam_accounts');

            $table->string('phone_number', 60);
            $table->string('address', 255);
            $table->decimal('latitude', 11, 8);
            $table->decimal('longitude', 11, 8);
            $table->string('area_manager', 100);
            $table->string('branch_head', 100);

            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by');
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('branches');
    }
};
