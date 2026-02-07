<?php

namespace App\Filament\Resources\Enrollments\Tables;

use App\Enums\EnrollmentStatus;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class EnrollmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('enrollment_number')
                    ->label('No. Pendaftaran')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),
                
                TextColumn::make('student_name')
                    ->label('Nama Siswa')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(function ($record) {
                        // Admin sees full name, non-admin sees masked
                        return $record->masked_student_name;
                    }),
                
                TextColumn::make('class.name')
                    ->label('Kelas')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info'),
                
                TextColumn::make('class.program.name')
                    ->label('Program')
                    ->searchable()
                    ->toggleable(),
                
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->sortable(),
                
                TextColumn::make('student_email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->formatStateUsing(function ($record) {
                        // Admin sees full email, non-admin sees masked
                        return $record->masked_student_email;
                    })
                    ->copyable(fn ($record) => !shouldMaskData() ? $record->student_email : null),
                
                TextColumn::make('student_phone')
                    ->label('Telepon')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->formatStateUsing(function ($record) {
                        // Admin sees full phone, non-admin sees masked
                        return $record->masked_student_phone;
                    })
                    ->copyable(fn ($record) => !shouldMaskData() ? $record->student_phone : null),
                
                TextColumn::make('student_age')
                    ->label('Usia')
                    ->suffix(' tahun')
                    ->sortable()
                    ->toggleable(),
                
                TextColumn::make('payment.status')
                    ->label('Status Pembayaran')
                    ->badge()
                    ->sortable()
                    ->default('Belum Bayar'),
                
                TextColumn::make('enrolled_at')
                    ->label('Tanggal Daftar')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                
                TextColumn::make('confirmed_at')
                    ->label('Tanggal Konfirmasi')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(EnrollmentStatus::class)
                    ->multiple(),
                
                SelectFilter::make('class_id')
                    ->label('Kelas')
                    ->relationship('class', 'name')
                    ->searchable()
                    ->preload(),
                
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('confirm')
                    ->label('Konfirmasi')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->status->value === 'pending')
                    ->action(function ($record) {
                        $record->update([
                            'status' => EnrollmentStatus::CONFIRMED,
                            'confirmed_at' => now(),
                        ]);
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('enrolled_at', 'desc');
    }
}
