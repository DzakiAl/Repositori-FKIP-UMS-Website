<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('password_file_downloads', function (Blueprint $table) {
            $table->id();
            $table->string('download_password');
            $table->timestamps();
        });

        // Insert default password
        DB::table('password_file_downloads')->insert([
            'download_password' => bcrypt('defaultpassword'),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('password_file_downloads');
    }
};
