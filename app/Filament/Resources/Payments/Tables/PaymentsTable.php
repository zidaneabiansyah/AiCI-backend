<?php

namespace App\Filament\Resources\Payments\Tables;

use App\Enums\PaymentStatus;
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

class PaymentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('invoice_number')
                    ->label('No. Invoice')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),
                
                TextColumn::make('enrollment.enrollment_number')
                    ->label('No. Pendaftaran')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                
                TextColumn::make('enrollment.student_name')
                    ->label('Nama Siswa')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(function ($record) {
                        // Admin sees full name, non-admin sees masked
                        return $record->enrollment?->masked_student_name ?? '-';
                    }),
                
                TextColumn::make('enrollment.class.name')
                    ->label('Kelas')
                    ->searchable()
                    ->badge()
                    ->color('info'),
                
                TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('IDR')
                    ->sortable()
                    ->weight('bold'),
                
                TextColumn::make('payment_method')
                    ->label('Metode')
                    ->searchable()
                    ->badge()
                    ->toggleable(),
                
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->sortable(),
                
                TextColumn::make('paid_at')
                    ->label('Tanggal Bayar')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->placeholder('Belum dibayar'),
                
                TextColumn::make('expired_at')
                    ->label('Kadaluarsa')
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
                    ->options(PaymentStatus::class)
                    ->multiple(),
                
                SelectFilter::make('payment_method')
                    ->label('Metode Pembayaran')
                    ->options([
                        'bank_transfer' => 'Bank Transfer',
                        'credit_card' => 'Credit Card',
                        'ewallet' => 'E-Wallet',
                        'qris' => 'QRIS',
                        'retail_outlet' => 'Retail Outlet',
                    ]),
                
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                Action::make('view_invoice')
                    ->label('Lihat Invoice')
                    ->icon('heroicon-o-document-text')
                    ->url(fn ($record) => $record->xendit_invoice_url)
                    ->openUrlInNewTab()
                    ->visible(fn ($record) => $record->xendit_invoice_url !== null && $record->xendit_invoice_url !== ''),
                Action::make('check_status')
                    ->label('Cek Status')
                    ->icon('heroicon-o-arrow-path')
                    ->color('info')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $paymentService = app(\App\Services\PaymentService::class);
                        $paymentService->checkPaymentStatus($record);
                    })
                    ->visible(fn ($record) => $record->status->value === 'pending'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
