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
        Schema::create('document_requests', function (Blueprint $table) {
            $table->id();
            // user yang membuat request
            $table->foreignId('user_id')->references('id')->on('users')->cascadeOnDelete()->cascadeOnUpdate();
            // user yang ditugaskan untuk menangani request
            $table->foreignId('assigned_to')->references('id')->on('users')->cascadeOnDelete()->cascadeOnUpdate()->nullable();
            $table->foreignId('document_id')->references('id')->on('documents')->cascadeOnDelete()->cascadeOnUpdate()->nullable();
            $table->string('request_document_title');
            $table->text('notes')->nullable();
            $table->boolean('is_internal_document')->default(false);
            $table->string('file')->nullable();
            $table->string('url')->nullable();
            $table->string('author')->nullable();
            $table->date('date')->nullable();
            $table->enum('status', ['pending', 'in_progress', 'completed', 'rejected'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_requests');
    }
};
