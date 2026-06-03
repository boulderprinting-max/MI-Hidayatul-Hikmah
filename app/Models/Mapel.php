<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mapel extends Model
{
    protected $fillable = ['kode', 'nama', 'tingkat', 'deskripsi', 'is_active'];
}
