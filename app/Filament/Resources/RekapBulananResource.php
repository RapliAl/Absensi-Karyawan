<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RekapBulananResource\Pages;
use App\Models\RekapBulanan;
use App\Services\RekapService;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;

class RekapBulananResource extends Resource
{
    protected static ?string $model = RekapBulanan::class;
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationLabel = '📊 REKAP BULANAN';
    protected static ?int $navigationSort = 2;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('periode')
                    ->label('Periode Bulan')
                    ->size('lg')
                    ->weight('bold')
                    ->sortable(['tahun', 'bulan']),
                    
                Tables\Columns\TextColumn::make('total_karyawan')
                    ->label('Total Karyawan')
                    ->alignCenter()
                    ->size('lg'),
                    
                Tables\Columns\TextColumn::make('total_hari_kerja')
                    ->label('Hari Kerja')
                    ->alignCenter()
                    ->size('lg'),
                    
                Tables\Columns\TextColumn::make('total_hadir')
                    ->label('Total Hadir')
                    ->color('success')
                    ->alignCenter()
                    ->weight('bold')
                    ->size('lg'),
                    
                Tables\Columns\TextColumn::make('total_sakit')
                    ->label('Total Sakit')
                    ->color('danger')
                    ->alignCenter()
                    ->weight('bold')
                    ->size('lg'),
                    
                Tables\Columns\TextColumn::make('total_izin')
                    ->label('Total Izin')
                    ->color('warning')
                    ->alignCenter()
                    ->weight('bold')
                    ->size('lg'),
                    
                Tables\Columns\TextColumn::make('total_alpha')
                    ->label('Total Alpha')
                    ->color('gray')
                    ->alignCenter()
                    ->weight('bold')
                    ->size('lg'),
                    
                Tables\Columns\TextColumn::make('persentase_kehadiran')
                    ->label('% Kehadiran')
                    ->formatStateUsing(fn($state) => $state . '%')
                    ->color(fn($state) => $state >= 90 ? 'success' : ($state >= 75 ? 'warning' : 'danger'))
                    ->weight('bold')
                    ->alignCenter()
                    ->size('lg'),
                    
                Tables\Columns\BadgeColumn::make('status')
                    ->colors(['success' => 'final', 'warning' => 'draft'])
                    ->size('lg'),
            ])
            ->actions([
                Action::make('export')
                    ->label('📄 Export Excel')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('primary')
                    ->button()
                    ->size('lg')
                    ->action(fn($record) => static::exportRekap($record)),
                    
                Action::make('detail')
                    ->label('👁️ Detail')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->button()
                    ->action(fn($record) => static::showDetail($record)),
            ])
            ->headerActions([
                Action::make('generate_current')
                    ->label('🔄 Generate Bulan Ini')
                    ->color('success')
                    ->icon('heroicon-o-calendar')
                    ->button()
                    ->size('xl')
                    ->action(fn() => static::generateCurrentMonth()),
                    
                Action::make('generate_last')
                    ->label('📅 Generate Bulan Lalu')
                    ->color('warning')
                    ->icon('heroicon-o-calendar-days')
                    ->button()
                    ->size('xl')
                    ->action(fn() => static::generateLastMonth()),
            ])
            ->defaultSort('tahun', 'desc')
            ->defaultSort('bulan', 'desc');
    }

    protected static function generateCurrentMonth()
    {
        try {
            // Untuk sementara kita buat simple tanpa RekapService
            Notification::make()
                ->title('✅ Rekap Bulan Ini Berhasil!')
                ->body("Fitur generate rekap akan segera tersedia")
                ->success()
                ->duration(8000)
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('❌ Error!')
                ->body('Gagal membuat rekap bulan ini: ' . $e->getMessage())
                ->danger()
                ->duration(8000)
                ->send();
        }
    }

    protected static function generateLastMonth()
    {
        try {
            // Untuk sementara kita buat simple tanpa RekapService
            Notification::make()
                ->title('✅ Rekap Bulan Lalu Berhasil!')
                ->body("Fitur generate rekap akan segera tersedia")
                ->success()
                ->duration(8000)
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('❌ Error!')
                ->body('Gagal membuat rekap bulan lalu: ' . $e->getMessage())
                ->danger()
                ->duration(8000)
                ->send();
        }
    }

    protected static function exportRekap($record)
    {
        try {
            return \Maatwebsite\Excel\Facades\Excel::download(
                new \App\Exports\RekapBulananExport($record),
                "rekap_bulanan_{$record->periode}.xlsx"
            );
        } catch (\Exception $e) {
            Notification::make()
                ->title('❌ Export Gagal!')
                ->body('Gagal mengexport rekap: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected static function showDetail($record)
    {
        Notification::make()
            ->title("📊 Detail Rekap {$record->periode}")
            ->body("
                👥 Karyawan: {$record->total_karyawan}
                📅 Hari Kerja: {$record->total_hari_kerja}
                ✅ Hadir: {$record->total_hadir}
                🏥 Sakit: {$record->total_sakit}
                📋 Izin: {$record->total_izin}
            ")
            ->info()
            ->duration(10000)
            ->send();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRekapBulanans::route('/'),
        ];
    }
}