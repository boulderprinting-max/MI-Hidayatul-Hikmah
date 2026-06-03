<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengaturanSekolah extends Model
{
    protected $fillable = ['nama_sekolah', 'nss', 'npsn', 'alamat', 'telepon', 'email', 'website', 'kepala_sekolah', 'nip_kepala_sekolah', 'logo', 'kop_surat'];
}
