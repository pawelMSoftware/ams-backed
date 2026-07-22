<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('resources_files', function (Blueprint $table) {
            $table->integer('disksize')->default(-1);
            $table->string('resolution', 12)->default('?');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('resources_files', function (Blueprint $table) {
            $table->dropColumn(['disksize', 'resolution']);
        });
    }
};
