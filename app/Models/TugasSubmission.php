<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TugasSubmission extends Model
{
    protected $fillable = ['tugas_id', 'siswa_id', 'file_jawaban', 'catatan_siswa', 'nilai', 'catatan_guru', 'submitted_at', 'graded_at'];

    public function tugas()
    {
        return $this->belongsTo(Tugas::class);
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }
}
