<?php

use App\Models\Paper;
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
        Schema::create('embedings', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Paper::class)->constrained()->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('embedding');
            $table->string('origin');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('embedings');
    }
};
