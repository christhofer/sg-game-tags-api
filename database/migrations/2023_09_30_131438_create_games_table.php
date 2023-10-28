<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->unsignedMediumInteger('app_id')->index()->nullable();
            $table->unsignedMediumInteger('package_id')->index()->nullable();
            $table->string('name');
            $table->unsignedInteger('cv_reduced_at')->nullable();
            $table->unsignedInteger('cv_removed_at')->nullable();
            $table->timestamp('last_checked_sg_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('games');
    }
};
