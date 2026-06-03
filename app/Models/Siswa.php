<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Siswa extends Model
{
    protected $fillable = ['user_id', 'nis', 'nisn', 'nama_lengkap', 'jenis_kelamin', 'tempat_lahir', 'tanggal_lahir', 'alamat', 'kelas_id', 'nama_ayah', 'nama_ibu', 'no_telepon_ortu', 'foto', 'status', 'tanggal_masuk'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    public function waliMurids()
    {
        return $this->belongsToMany(WaliMurid::class, 'siswa_wali_murid');
    }
}
