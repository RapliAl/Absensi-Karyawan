<?php

namespace App\Exports;

use App\Models\Absensi;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AbsensiBulananExport implements FromCollection, WithHeadings, WithStyles
{
    protected $month;
    protected $year;

    public function __construct($month = null, $year = null)
    {
        $this->month = $month ?? Carbon::now()->month;
        $this->year = $year ?? Carbon::now()->year;
    }
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $startDate = Carbon::create($this->year, $this->month, 1)->startOfMonth();
        $endDate = Carbon::create($this->year, $this->month, 1)->endOfMonth();

        return Absensi::with('karyawan')
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->orderBy('tanggal')
            ->orderBy('karyawan_id')
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
