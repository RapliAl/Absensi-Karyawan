<?php

namespace App\Exports;

use App\Models\RekapBulanan;
use App\Models\Absensi;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Carbon\Carbon;

class RekapBulananExport implements WithMultipleSheets
{
    protected $rekap;

    public function __construct(RekapBulanan $rekap)
    {
        $this->rekap = $rekap;
    }

    public function sheets(): array
    {
        return [
            new RekapSummarySheet($this->rekap),
            new RekapDetailSheet($this->rekap),
        ];
    }
}

class RekapSummarySheet implements FromCollection, WithHeadings, WithStyles, WithTitle
{
    protected $rekap;
    
    public function __construct(RekapBulanan $rekap)
    {
        $this->rekap = $rekap;
    }

    public function collection()
    {
        return collect ([
            [
                'Bulan' => $this->rekap->bulan,
                'Tahun' => $this->rekap->tahun,
                'Total Hadir' => $this->rekap->total_hadir,
                'Total Sakit' => $this->rekap->total_sakit,
                'Total Izin' => $this->rekap->total_izin,
            ]
        ]);
    }

    public function headings(): array
    {
        return [
            'Bulan',
            'Tahun',
            'Total Hadir',
            'Total Sakit',
            'Total Izin',
        ];
    }

    public function title(): string
    {
        return 'Rekap Bulanan Absensi';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}

class RekapDetailSheet implements FromCollection, WithHeadings, WithStyles, WithTitle
{
    protected $rekap;

    public function __construct(RekapBulanan $rekap)
    {
        $this->rekap = $rekap;
    }

    public function collection()
    {
        $startDate = Carbon::create($this->rekap->tahun, $this->rekap->bulan, 1)->startOfMonth();
        $endDate = Carbon::create($this->rekap->tahun, $this->rekap->bulan, 1)->endOfMonth();

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

    public function title(): string
    {
        return 'Detail Absensi';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}