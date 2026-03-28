<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Logbook extends Model
{
    use HasFactory;

    protected $fillable = [
        'thesis_id',
        'activity_date',
        'topic',
        'description',
        'file_path',
        'feedback',
        'status',
    ];

    public function thesis()
    {
        return $this->belongsTo(Thesis::class);
    }
}