<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kelas extends Model
{
    protected $fillable = ['nama_kelas', 'tingkat', 'guru_id', 'tahun_ajaran_id', 'kapasitas'];

    public function guru()
    {
        return $this->belongsTo(Guru::class);
    }

    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class);
    }

    public function siswas()
    {
        return $this->hasMany(Siswa::class);
    }
}
