<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('au_post_sources', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_active')->default(1);
            $table->integer('kind')->default(1);
            $table->string('webservice')->nullable();
            $table->text('available_targets')->nullable();
            $table->integer('share_rate')->default(50);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('au_post_sources');
    }
};
