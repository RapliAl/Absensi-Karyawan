<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Karyawan extends Model
{
    protected $fillable = [
        'nama',
    ];

    public function absensis()
    {
        return $this->hasMany(Absensi::class);
    }

    public function absensiHariIni()
    {
        return $this->hasOne(Absensi::class)->whereDate('tanggal', today());
    }

    public function getStatusHariIniAttribute()
    {
        return $this->absensiHariIni?->status ?? null;
    }

    public function getAbsen()
    {
        return $this->absensiHariIni()->exists();
    }

    public function getAbsensiBulanIni()
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        return $this->absensis()
            ->whereBetween('tanggal', [$startOfMonth, $endOfMonth])
            ->get();
    }
}
