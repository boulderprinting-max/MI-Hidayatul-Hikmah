<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TahunAjaran extends Model
{
    protected $fillable = ['nama', 'semester', 'is_active', 'tanggal_mulai', 'tanggal_selesai'];

    public function kelas()
    {
        return $this->hasMany(Kelas::class);
    }
}
