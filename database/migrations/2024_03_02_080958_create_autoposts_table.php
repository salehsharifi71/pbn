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
        Schema::create('autoposts', function (Blueprint $table) {
            $table->id();
            $table->integer('source_id');
            $table->string('target');
            $table->string('url');
            $table->string('title');
            $table->string('img');
            $table->text('content');
            $table->integer('status')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('autoposts');
    }
};
