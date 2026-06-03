<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Materi extends Model
{
    protected $fillable = ['judul', 'deskripsi', 'kelas_id', 'mapel_id', 'guru_id', 'tipe_file', 'file_path', 'youtube_url', 'tahun_ajaran_id', 'is_published'];

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    public function mapel()
    {
        return $this->belongsTo(Mapel::class);
    }

    public function guru()
    {
        return $this->belongsTo(Guru::class);
    }
}
