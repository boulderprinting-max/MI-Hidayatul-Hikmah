<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RaporNilai extends Model
{
    protected $fillable = ['rapor_id', 'mapel_id', 'nilai_pengetahuan', 'nilai_keterampilan', 'predikat', 'deskripsi'];

    public function rapor()
    {
        return $this->belongsTo(Rapor::class);
    }

    public function mapel()
    {
        return $this->belongsTo(Mapel::class);
    }
}
