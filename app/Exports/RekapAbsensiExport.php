<?php

namespace App\Exports;

use App\Models\Karyawan;
use App\Models\Absensi;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

class RekapAbsensiExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $bulan;
    protected $tahun;

    public function __construct($bulan, $tahun)
    {
        $this->bulan = $bulan;
        $this->tahun = $tahun;
    }

    public function collection()
    {
        return Karyawan::with(['absensis' => function ($query) {
            $query->whereYear('tanggal', $this->tahun)
                  ->whereMonth('tanggal', $this->bulan);
        }])->get();
    }

    public function headings(): array
    {
        $namaBulan = Carbon::create($this->tahun, $this->bulan, 1)->format('F Y');
        return [
            ['REKAP ABSENSI BULAN ' . strtoupper($namaBulan)],
            [''],
            ['No', 'Nama Karyawan', 'Hadir', 'Sakit', 'Ijin', 'Total Hari Kerja']
        ];
    }

    public function map($karyawan): array
    {
        static $no = 0;
        $no++;

        // Hitung total per status
        $hadir = $karyawan->absensis->where('status', 'Hadir')->count();
        $sakit = $karyawan->absensis->where('status', 'Sakit')->count();
        $ijin = $karyawan->absensis->where('status', 'Izin')->count();
        
        // Hitung alfa (karyawan yang tidak absen sama sekali di hari kerja)
        $totalAbsensi = $karyawan->absensis->count();
        $totalHariKerja = $this->hitungHariKerja($this->bulan, $this->tahun);

        return [
            $no,
            $karyawan->nama,
            $hadir > 0 ? $hadir . ' hari' : '-',
            $sakit > 0 ? $sakit . ' hari' : '-', 
            $ijin > 0 ? $ijin . ' hari' : '-',
            $totalHariKerja . ' hari'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
            3 => ['font' => ['bold' => true]],
        ];
    }

    private function hitungHariKerja($bulan, $tahun)
    {
        $startDate = Carbon::create($tahun, $bulan, 1);
        $endDate = $startDate->copy()->endOfMonth();
        $hariKerja = 0;

        while ($startDate <= $endDate) {
            // Hitung hari kerja (Senin-Jumat)
            if ($startDate->isWeekday()) {
                $hariKerja++;
            }
            $startDate->addDay();
        }

        return $hariKerja;
    }
}
