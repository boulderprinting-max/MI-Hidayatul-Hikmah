<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WaliMurid extends Model
{
    protected $fillable = ['user_id', 'nama_lengkap', 'hubungan', 'no_telepon', 'alamat', 'pekerjaan'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function siswas()
    {
        return $this->belongsToMany(Siswa::class, 'siswa_wali_murid');
    }
}
