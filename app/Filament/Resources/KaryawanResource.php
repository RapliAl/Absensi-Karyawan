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

class KaryawanResource extends Resource
{
    protected static ?string $model = Karyawan::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $modelLabel = 'Absensi Harian';
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
                    ->getStateUsing(fn (Karyawan $record): string => $record->status ?? '-')
                    ->colors([
                        'secondary' => '-',
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
                    ->modalSubheading(fn($record) => "Apakah {$record->nama} Hadir Hari Ini?")
                    ->action(fn(Karyawan $record) => static::markAbsensi($record, 'hadir')),
                
                Action::make('Sakit')
                    ->label('Sakit')
                    ->button()
                    ->color('danger')
                    ->visible(fn (Karyawan $record) => !$record->getAbsen())
                    ->requiresConfirmation()
                    ->modalHeading('Konfirmasi Kehadiran')
                    ->modalSubheading(fn($record) => "Apakah {$record->nama} Sakit Hari Ini?")
                    ->action(fn(Karyawan $record) => static::markAbsensi($record, 'sakit')),
                
                Action::make('Izin')
                    ->label('Izin')
                    ->button()
                    ->color('warning')
                    ->visible(fn (Karyawan $record) => !$record->getAbsen())
                    ->requiresConfirmation()
                    ->modalHeading('Konfirmasi Kehadiran')
                    ->modalSubheading(fn($record) => "Apakah {$record->nama} Izin Hari Ini?")
                    ->action(fn(Karyawan $record) => static::markAbsensi($record, 'izin'))

            ])
            ->headerActions([
                Action::make('export_hari_ini')
                    ->label('Download Absensi Hari Ini')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('primary')
                    ->action(fn() => static::exportAbsensiHariIni()),
                
                Action::make('export_bulan_ini')
                    ->label('Download Absensi Bulan Ini')
                    ->icon('heroicon-o-calendar-days')
                    ->color('success')
                    ->action(fn() => static::exportAbsensiBulanIni()),
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
