<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rapor extends Model
{
    protected $fillable = ['siswa_id', 'kelas_id', 'tahun_ajaran_id', 'semester', 'total_hadir', 'total_izin', 'total_sakit', 'total_alfa', 'catatan_wali_kelas', 'catatan_kepala_sekolah', 'ranking', 'is_published'];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    public function raporNilais()
    {
        return $this->hasMany(RaporNilai::class);
    }
}
