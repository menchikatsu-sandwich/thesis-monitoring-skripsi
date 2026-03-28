<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('logbooks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('thesis_id')->constrained('theses')->onDelete('cascade');
            $table->date('activity_date');
            $table->string('topic');
            $table->text('description');
            
            // Kolom Baru untuk Upload Draft Skripsi (bisa dipisah atau digabung di logbook terbaru)
            // Untuk simplifikasi, kita anggap upload draft utama ada di tabel 'theses', 
            // tapi logbook bisa punya lampiran kecil.
            $table->string('file_path')->nullable(); 
            
            $table->text('feedback')->nullable(); // Catatan revisi dosen
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('logbooks');
    }
};