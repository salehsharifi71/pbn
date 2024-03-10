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
        Schema::create('au_post_ques', function (Blueprint $table) {
            $table->id();
            $table->integer('kind')->default(1);
            $table->integer('source_id');
            $table->string('target');
            $table->string('url');
            $table->string('title')->nullable();
            $table->string('img')->nullable();
            $table->text('content')->nullable();
            $table->text('clearContent')->nullable();
            $table->text('keywords')->nullable();
            $table->integer('status')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('au_post_ques');
    }
};
