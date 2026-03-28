<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('theses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->string('title');
            $table->text('abstract')->nullable();
            
            // Status Alur Lengkap
            $table->enum('status', [
                'pengajuan_awal',      // Baru isi judul
                'menunggu_plotting',   // Admin belum pilih dosen
                'pengerjaan_skripsi',  // Sudah ada dosen, mulai bimbingan
                'bimbingan_aktif',     // Sedang upload logbook/draft
                'perlu_revisi',        // Dosen minta revisi
                'acc_pembimbing',      // Sudah ACC
                'siap_sidang',         // Sudah dijadwalkan admin
                'lulus'               // Selesai
            ])->default('pengajuan_awal');
            
            $table->foreignId('lecturer_1_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('lecturer_2_id')->nullable()->constrained('users')->onDelete('set null');
            
            $table->string('draft_file_path')->nullable(); // File PDF Terakhir
            $table->text('lecturer_notes')->nullable();    // Catatan Revisi Terakhir
            
            $table->date('proposal_date')->nullable();
            $table->date('final_exam_date')->nullable();
            $table->time('exam_time')->nullable();
            $table->string('exam_room')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('theses');
    }
};