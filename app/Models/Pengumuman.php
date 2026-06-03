<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengumuman extends Model
{
    protected $fillable = ['judul', 'konten', 'target', 'target_kelas_id', 'file_lampiran', 'author_id', 'is_published', 'published_at'];

    public function targetKelas()
    {
        return $this->belongsTo(Kelas::class, 'target_kelas_id');
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
