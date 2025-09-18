<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Absensi extends Model
{
    protected $fillable = [
        'karyawan_id', 'tanggal', 'status', 'jam_absen'
    ];

    protected $casts = [
        'tanggal' => 'date',
        'jam_absen' => 'datetime:H:i',
    ];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class);
    }
}
