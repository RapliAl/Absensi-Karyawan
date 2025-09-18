<?php

namespace App\Exports;

use App\Models\Absensi;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AbsensiHarianExport implements FromCollection, WithHeadings, WithStyles
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Absensi::with('karyawan')
            ->whereDate('tanggal', today())
            ->get()
            ->map(function ($absensi) {
                return [
                    'ID' => $absensi->karyawan->id,
                    'Nama' => $absensi->karyawan->nama,
                    'Tanggal' => $absensi->tanggal->format('d/m/Y'),
                    'Hari' => $absensi->tanggal->locale('id')->dayName,
                    'Status' => strtoupper($absensi->status),
                    'Jam Absen' => $absensi->jam_absen->format('H:i'),
                ];
            });
    }

    public function headings(): array
    {
        return [
            'ID',
            'Nama',
            'Tanggal',
            'Hari',
            'Status',
            'Jam Absen',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
