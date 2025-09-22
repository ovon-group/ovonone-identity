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
        Schema::create('application_environments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->onDelete('cascade');
//            $table->string('environment'); // e.g., "production", "development", "staging"
            $table->string('name'); // e.g., "Production", "Development", "Feature Branch"
            $table->string('url'); // Full URL for this environment
            $table->foreignUuid('client_id')->nullable()->constrained('oauth_clients')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('application_environments');
    }
};
