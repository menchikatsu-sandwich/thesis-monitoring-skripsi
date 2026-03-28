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
        $steps = ['proposal', 'seminar_hasil', 'sidang', 'revisi', 'acc_pembimbing', 'siap_sidang', 'lulus'];
        $current = array_search($this->status, $steps);
        if ($current === false) return 0;
        return min(100, ($current + 1) * 15); 
    }
    
    public function isAccByLecturer() {
        return in_array($this->status, ['acc_pembimbing', 'siap_sidang', 'lulus']);
    }
}