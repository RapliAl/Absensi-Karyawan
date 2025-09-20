<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KaryawanResource\Pages;
use App\Filament\Resources\KaryawanResource\RelationManagers;
use App\Models\Karyawan;
use App\Models\Absensi;
use App\Exports\RekapAbsensiExport;
use Filament\Tables\Actions\Action;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Notifications\Notification;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
class KaryawanResource extends Resource
{
    protected static ?string $model = Karyawan::class;
    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    protected static ?string $modelLabel = "Absensi";
    
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('nama')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama')
                    ->label('Nama')
                    ->weight('bold')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->getStateUsing(fn (Karyawan $record): string => $record->absensiHariIni?->status ?? 'Tidak Hadir')
                    ->colors([
                        'secondary' => 'Tidak Hadir',
                        'success' => 'Hadir',
                        'warning' => 'Izin',
                        'danger' => 'Sakit',
                    ])
                    ->searchable(false)
                    ->sortable(false),
                TextColumn::make('absensiHariIni.jam_absen')
                    ->label('Jam Absen')
                    ->dateTime('H:i')
                    ->placeholder('-')
                    ->sortable()
            ])
            ->filters([
                //
            ])
            ->actions([
                Action::make('Hadir')
                    ->label('Hadir')
                    ->button()
                    ->color('success')
                    ->visible(fn (Karyawan $record) => !$record->getAbsen())
                    ->requiresConfirmation()
                    ->modalHeading('Konfirmasi Kehadiran')
                    ->modalDescription(fn($record) => "Apakah {$record->nama} Hadir Hari Ini?")
                    ->action(function (Karyawan $record) {
                        try {
                            $existingAbsensi = Absensi::where('karyawan_id', $record->karyawan_id)
                                ->whereDate('tanggal', today())
                                ->first();
                            
                            if ($existingAbsensi) {
                                Notification::make()
                                    ->title('Anda Sudah Absen!')
                                    ->warning()
                                    ->body("Absensi untuk {$record->nama} sudah tercatat hari ini dengan status:" . strtoupper($existingAbsensi->status))
                                    ->duration(5000)
                                    ->send();

                                return;
                            }

                            Absensi::create([
                                'karyawan_id' => $record->id,
                                'tanggal' => today(),
                                'status' => 'Hadir',
                                'jam_absen' => now()->format('H:i:s'),
                            ]);

                            Notification::make()
                                ->title ('Absensi Berhasil!')
                                ->success()
                                ->body("Terima Kasih {$record->nama}, status hadir telah tercatat pada : ". now()->format('H:i'))
                                ->duration(5000)
                                ->send();
                        } catch (QueryException $e) {
                            Log::error('Data Absensi Gagal Disimpan:' . $e->getMessage());

                            Notification::make()
                                ->title('Gagal Menyimpan!')
                                ->body('Terjadi kesalahan database. Silakan coba lagi atau hubungi admin.')
                                ->danger()
                                ->duration(10000)
                                ->send();
                        } catch (\Exception $e) {
                            // Handle other errors
                            Log::error('Absensi General Error: ' . $e->getMessage());
                            
                            Notification::make()
                                ->title('Error!')
                                ->body('Gagal menyimpan absensi. Silakan coba lagi.')
                                ->danger()
                                ->duration(8000)
                                ->send();
                        }
                    }),
                
                Action::make('sakit')
                    ->label('Sakit')
                    ->button()
                    ->color('danger')
                    ->visible(fn (Karyawan $record) => !$record->getAbsen())
                    ->requiresConfirmation()
                    ->modalHeading('Konfirmasi Sakit')
                    ->modalDescription(fn($record) => "Apakah {$record->nama} SAKIT hari ini?")
                    ->action(function (Karyawan $record) {
                        try {
                            Absensi::create([
                                'karyawan_id' => $record->id,
                                'tanggal' => today(),
                                'status' => 'Sakit',
                                'jam_absen' => now()->format('H:i:s'),
                            ]);

                            Notification::make()
                                ->title('SAKIT TERCATAT!')
                                ->body("Status SAKIT untuk {$record->nama} telah tercatat")
                                ->success()
                                ->duration(8000)
                                ->send();

                        } catch (\Exception $e) {
                            Log::error('Absensi Sakit Error: ' . $e->getMessage());
                            
                            Notification::make()
                                ->title('❌ Error!')
                                ->body('Gagal menyimpan status sakit. Silakan coba lagi.')
                                ->danger()
                                ->send();
                        }
                    }),
                
                Action::make('izin')
                    ->label('Izin')
                    ->button()
                    ->color('warning')
                    ->visible(fn (Karyawan $record) => !$record->getAbsen())
                    ->requiresConfirmation()
                    ->modalHeading('Konfirmasi Izin')
                    ->modalDescription(fn($record) => "Apakah {$record->nama} IZIN hari ini?")
                    ->action(function (Karyawan $record) {
                        try {
                            Absensi::create([
                                'karyawan_id' => $record->id,
                                'tanggal' => today(),
                                'status' => 'Izin',
                                'jam_absen' => now()->format('H:i:s'),
                            ]);

                            Notification::make()
                                ->title('📋 IZIN TERCATAT!')
                                ->body("Status IZIN untuk {$record->nama} telah tercatat")
                                ->success()
                                ->duration(8000)
                                ->send();

                        } catch (\Exception $e) {
                            Log::error('Absensi Izin Error: ' . $e->getMessage());
                            
                            Notification::make()
                                ->title('❌ Error!')
                                ->body('Gagal menyimpan status izin. Silakan coba lagi.')
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->headerActions([    
                Action::make('export_rekap_bulanan')
                    ->label('Export Rekap Bulanan')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->color('warning')
                    ->form([
                        Forms\Components\Select::make('bulan')
                            ->label('Bulan')
                            ->options([
                                1 => 'Januari',
                                2 => 'Februari',
                                3 => 'Maret',
                                4 => 'April',
                                5 => 'Mei',
                                6 => 'Juni',
                                7 => 'Juli',
                                8 => 'Agustus',
                                9 => 'September',
                                10 => 'Oktober',
                                11 => 'November',
                                12 => 'Desember',
                            ])
                            ->default(now()->month)
                            ->required(),
                        
                        Forms\Components\Select::make('tahun')
                            ->label('Tahun')
                            ->options(self::getTahunOptions())
                            ->default(now()->year)
                            ->required()
                            ->searchable()
                            ->placeholder('Pilih Tahun'),
                    ])
                    ->action(function (array $data) {
                        $namaBulan = Carbon::create($data['tahun'], $data['bulan'], 1)->format('F_Y');
                        return Excel::download(new RekapAbsensiExport($data['bulan'], $data['tahun']),
                        "Rekap_Absensi_{$namaBulan}.xlsx");
                    })
            ])

            ->defaultSort('nama')

            ->searchPlaceholder('Cari Nama')

            ->poll('15s')

            ->striped()

            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    protected static function markAbsensi(Karyawan $karyawan, string $status): void
    {
        try {
            $now = Carbon::now();
            $workingDate = $now->hour >= 8 ? $now->toDateString() : $now->copy()->subDay()->toDateString();
            
            // Cek apakah sudah ada absensi untuk working date ini
            $existingAbsensi = $karyawan->absensis()
                ->whereDate('tanggal', $workingDate)
                ->first();
            
            if ($existingAbsensi) {
                // Update status yang sudah ada
                $existingAbsensi->update([
                    'status' => $status,
                    'jam_absen' => now()->format('H:i:s')
                ]);
                $action = 'diupdate';
            } else {
                // Buat absensi baru
                Absensi::create([
                    'karyawan_id' => $karyawan->id,
                    'tanggal' => $workingDate,
                    'status' => $status,
                    'jam_absen' => now()->format('H:i:s')
                ]);
                $action = 'ditambahkan';
            }
            
            // Clear cache widget
            \Illuminate\Support\Facades\Cache::forget("dashboard_absensi_stats_{$workingDate}");
            
            Notification::make()
                ->title('Absensi Berhasil!')
                ->success()
                ->body("Terima Kasih {$karyawan->nama}, status '{$status}' telah {$action} untuk tanggal {$workingDate} pada ". now()->format('H:i'))
                ->duration(5000)
                ->send();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error marking absensi: ' . $e->getMessage());
            
            Notification::make()
                ->title('Absensi Gagal!')
                ->danger()
                ->body('Terjadi kesalahan saat mencatat absensi: ' . $e->getMessage())
                ->duration(5000)
                ->send();
        }
    }

    protected static function exportAbsensiHariIni()
    {
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\AbsensiHarianExport, 'absensi_hari_ini.xlsx');
    }

    protected static function exportAbsensiBulanIni()
    {
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\AbsensiBulananExport, 'absensi_bulan_ini.xlsx');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListKaryawans::route('/'),
            'create' => Pages\CreateKaryawan::route('/create'),
            'edit' => Pages\EditKaryawan::route('/{record}/edit'),
        ];
    }

    private static function getTahunOptions(): array
    {
        $startYear = 2025;
        $currentYear = now()->year;
        $endYear = $currentYear + 100;

        $years = range($startYear, $endYear);
        return array_combine($years, $years);
    }
}
