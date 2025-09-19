<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KaryawanResource\Pages;
use App\Filament\Resources\KaryawanResource\RelationManagers;
use App\Models\Karyawan;
use App\Models\Absensi;
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
class KaryawanResource extends Resource
{
    protected static ?string $model = Karyawan::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';

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
                    ]),
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
                Action::make('export_hari_ini')
                    ->label(' Export Hari Ini')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('primary')
                    ->action(function () {
                        try {
                            return response()->streamDownload(function () {
                                echo \Maatwebsite\Excel\Facades\Excel::raw(
                                    new \App\Exports\AbsensiHarianExport(),
                                    \Maatwebsite\Excel\Excel::XLSX
                                );
                            }, 'absensi_harian_' . today()->format('Y-m-d') . '.xlsx');
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('❌ Export Gagal!')
                                ->body('Gagal mengexport data: ' . $e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                
                Action::make('export_bulan_ini')
                    ->label('Export Bulan Ini')
                    ->icon('heroicon-o-calendar-days')
                    ->color('success')
                    ->action(function () {
                        try {
                            return response()->streamDownload(function () {
                                echo \Maatwebsite\Excel\Facades\Excel::raw(
                                    new \App\Exports\AbsensiBulananExport(),
                                    \Maatwebsite\Excel\Excel::XLSX
                                );
                            }, 'absensi_bulanan_' . now()->format('Y-m') . '.xlsx');
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('❌ Export Gagal!')
                                ->body('Gagal mengexport data: ' . $e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
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
            Absensi::create([
                'nama' => $karyawan->nama,
                'tanggal' => today(),
                'status' => $status,
                'jam_absen' => now(),
            ]);
            Notification::make()
                ->title('Absensi Berhasil!')
                ->success()
                ->body("Terima Kasih {$karyawan->nama}, status '{$status}' telah tercatat pada". now()->format('H:i'))
                ->duration(5000)
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Absensi Gagal!')
                ->danger()
                ->body('Terjadi kesalahan saat mencatat absensi. Silakan coba lagi.')
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
}
