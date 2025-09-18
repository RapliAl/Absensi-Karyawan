<?php

namespace App\Exports;

use App\Models\Absensi;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AbsensiBulananExport implements FromCollection, WithHeadings, WithStyles, WithTitle
{
    protected $month;
    protected $year;

    public function __construct($month = null, $year = null)
    {
        $this->month = $month ?? Carbon::now()->month;
        $this->year = $year ?? Carbon::now()->year;
    }

    public function collection()
    {
        $startDate = Carbon::create($this->year, $this->month, 1)->startOfMonth();
        $endDate = Carbon::create($this->year, $this->month, 1)->endOfMonth();

        $data = Absensi::with(['karyawan'])
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->orderBy('tanggal')
            ->orderBy('karyawan_id')
            ->get();

        if ($data->isEmpty()) {
            return collect([
                [
                    1,
                    'Tidak ada data absensi untuk bulan ini',
                    '-',
                    $startDate->format('d/m/Y') . ' - ' . $endDate->format('d/m/Y'),
                    '-',
                    '-',
                    '-'
                ]
            ]);
        }

        return $data->map(function ($absensi, $index) {
            return [
                'no' => $index + 1,
                'nama' => $absensi->karyawan->nama ?? 'Unknown',
                'tanggal' => $absensi->tanggal->format('d/m/Y'),
                'hari' => $absensi->tanggal->locale('id')->dayName,
                'status' => strtoupper($absensi->status),
                'jam_absen' => $absensi->jam_absen ? Carbon::parse($absensi->jam_absen)->format('H:i') : '-',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'NO',
            'NAMA KARYAWAN', 
            'TANGGAL', 
            'HARI', 
            'STATUS', 
            'JAM ABSEN'
        ];
    }

    public function title(): string
    {
        $bulanNama = Carbon::create($this->year, $this->month, 1)->locale('id')->monthName;
        return "Absensi {$bulanNama} {$this->year}";
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 12],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFE2EFDA']
                ]
            ],
        ];
    }
}