<?php

namespace App\Exports;

use App\Models\Absensi;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class AbsensiHarianExport implements FromCollection, WithHeadings, WithStyles, WithColumnFormatting
{
    public function collection()
    {
        $data = Absensi::with(['karyawan'])
            ->whereDate('tanggal', today())
            ->orderBy('karyawan_id')
            ->get();

        if ($data->isEmpty()) {
            // Return empty row with message if no data
            return collect([
                [
                    'Tidak ada data absensi untuk hari ini',
                    '',
                    today()->format('d/m/Y'),
                    '',
                    ''
                ]
            ]);
        }

        return $data->map(function ($absensi, $index) {
            return [
                'no' => $index + 1,
                'nama' => $absensi->karyawan->nama ?? 'Unknown',
                'tanggal' => $absensi->tanggal->format('d/m/Y'),
                'status' => strtoupper($absensi->status),
                'jam_absen' => $absensi->jam_absen ? \Carbon\Carbon::parse($absensi->jam_absen)->format('H:i') : '-',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'NO',
            'NAMA KARYAWAN', 
            'TANGGAL', 
            'STATUS', 
            'JAM ABSEN'
        ];
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

    public function columnFormats(): array
    {
        return [
            'C' => NumberFormat::FORMAT_TEXT, // NIP as text
            'D' => NumberFormat::FORMAT_DATE_DDMMYYYY, // Date format
        ];
    }
}