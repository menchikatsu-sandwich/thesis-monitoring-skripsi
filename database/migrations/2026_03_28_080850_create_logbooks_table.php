<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        
        if (!Schema::hasTable('logbooks')) {
            Schema::create('logbooks', function (Blueprint $table) {
                $table->id();
                $table->foreignId('thesis_id')->constrained('theses')->onDelete('cascade');
                $table->string('activity_type')->default('upload_draft'); // 'upload_draft', 'revisi_dosen'
                $table->string('file_path')->nullable(); // Link file saat itu
                $table->text('feedback')->nullable(); // Komentar dosen (copy dari lecturer_notes saat itu)
                $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
                $table->timestamps();
            });
        } else {
            // Tambah kolom jika belum ada (untuk keamanan jika tabel sudah exist)
            Schema::table('logbooks', function (Blueprint $table) {
                if (!Schema::hasColumn('logbooks', 'activity_type')) {
                    $table->string('activity_type')->default('upload_draft')->after('thesis_id');
                }
                // Pastikan kolom lain ada
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('logbooks');
    }
};