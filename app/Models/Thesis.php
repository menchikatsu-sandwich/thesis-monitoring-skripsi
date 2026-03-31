<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Thesis extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id', 'title', 'abstract', 'status', 'lecturer_1_id', 'lecturer_2_id',
        'proposal_date', 'final_exam_date', 'exam_time', 'exam_room',
        'draft_file_path', 'lecturer_notes'
    ];

    public function student() { return $this->belongsTo(User::class, 'student_id'); }
    public function lecturer1() { return $this->belongsTo(User::class, 'lecturer_1_id'); }
    public function lecturer2() { return $this->belongsTo(User::class, 'lecturer_2_id'); }
    public function logbooks() { return $this->hasMany(Logbook::class); }

    public function getProgressPercentageAttribute()
    {
        // Urutan status tetap, revisi tidak mundurkan progress utama
        $steps = [
            'menunggu_plotting', 
            'pengajuan_awal', 
            'bimbingan_aktif', // Tahap bimbingan aktif
            'acc_pembimbing', 
            'siap_sidang', 
            'lulus'
        ];

        $currentStatus = $this->status;

        // Jika status revisi, anggap posisinya sama dengan bimbingan_aktif (tidak mundur)
        if ($currentStatus === 'perlu_revisi') {
            $currentStatus = 'bimbingan_aktif';
        }

        $index = array_search($currentStatus, $steps);
        
        if ($index === false) return 0;
        
        // Hitung persen (total 5 langkah utama sebelum lulus)
        $totalSteps = count($steps) - 1; 
        return round(($index / $totalSteps) * 100);
    }
    
    public function isWaitingForPlotting() {
        return $this->status === 'menunggu_plotting';
    }

    public function isInGuidance() {
        return in_array($this->status, ['pengerjaan_skripsi', 'perlu_revisi']);
    }
}