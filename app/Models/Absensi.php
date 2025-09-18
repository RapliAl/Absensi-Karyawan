<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Absensi extends Model
{
    protected $fillable = [
        'karyawan_id',
        'tanggal', 
        'status',
        'jam_absen'
    ];

    protected $casts = [
        'tanggal' => 'date',
        'jam_absen' => 'string',
    ];

    public function karyawan(): BelongsTo
    {
        return $this->belongsTo(Karyawan::class);
    }
}