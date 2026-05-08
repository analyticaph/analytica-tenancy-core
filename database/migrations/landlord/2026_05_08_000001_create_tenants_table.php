<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('status')->default('active');
            $table->string('database')->unique();
            $table->string('db_host')->nullable();
            $table->unsignedInteger('db_port')->nullable();
            $table->string('db_username')->nullable();
            $table->text('db_password')->nullable();
            $table->string('db_driver')->nullable();
            $table->string('db_charset')->nullable();
            $table->string('db_collation')->nullable();
            $table->json('settings')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
