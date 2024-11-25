<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('background_jobs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('class_name');
            $table->string('method_name');
            $table->json('parameters')->nullable();
            $table->string('priority')->default('medium');
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->string('status')->default('pending');
            $table->integer('attempts')->default(0);
            $table->integer('max_attempts');
            $table->text('error')->nullable();
            $table->timestamps();

            // Indexes for efficient querying
            $table->index(['status', 'priority', 'scheduled_at']);
            $table->index(['status', 'started_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('background_jobs');
    }
};
