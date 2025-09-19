<?php

namespace App\Exports;

use App\Models\RekapBulanan;
use App\Models\Absensi;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
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
        return collect([
            [
                'periode' => $this->rekap->periode,
                'total_karyawan' => $this->rekap->total_karyawan,
                'total_hadir' => $this->rekap->total_hadir,
                'total_sakit' => $this->rekap->total_sakit,
                'total_izin' => $this->rekap->total_izin,
            ]
        ]);
    }

    public function headings(): array
    {
        return [
            'PERIODE', 'TOTAL KARYAWAN', 
            'TOTAL HADIR', 'TOTAL SAKIT', 'TOTAL IZIN',
        ];
    }

    public function title(): string
    {
        return 'Summary';
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
                    'nama' => $absensi->karyawan->nama,
                    'tanggal' => $absensi->tanggal->format('d/m/Y'),
                    'hari' => $absensi->tanggal->locale('id')->dayName,
                    'status' => strtoupper($absensi->status),
                    'jam_absen' => $absensi->jam_absen->format('H:i'),
                ];
            });
    }

    public function headings(): array
    {
        return ['NAMA', 'TANGGAL', 'HARI', 'STATUS', 'JAM ABSEN'];
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