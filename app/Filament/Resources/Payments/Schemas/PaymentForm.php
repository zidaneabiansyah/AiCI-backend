<?php

namespace App\Filament\Resources\Payments\Schemas;

use App\Enums\PaymentStatus;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class PaymentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('invoice_number')
                    ->required(),
                Select::make('enrollment_id')
                    ->relationship('enrollment', 'id')
                    ->required(),
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                TextInput::make('amount')
                    ->required()
                    ->numeric(),
                TextInput::make('admin_fee')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('total_amount')
                    ->required()
                    ->numeric(),
                TextInput::make('currency')
                    ->required()
                    ->default('IDR'),
                TextInput::make('payment_method')
                    ->required(),
                TextInput::make('payment_channel'),
                Select::make('status')
                    ->options(PaymentStatus::class)
                    ->default('pending')
                    ->required(),
                TextInput::make('xendit_invoice_id')
                    ->formatStateUsing(function ($state, $record) {
                        // Saat view/edit, tampilkan masked untuk non-admin
                        if ($record && shouldMaskData()) {
                            return maskSensitiveData($state, 10, 0);
                        }
                        return $state;
                    })
                    ->disabled(fn ($record) => $record && shouldMaskData()), // Non-admin can't edit
                TextInput::make('xendit_invoice_url')
                    ->url(),
                TextInput::make('xendit_external_id')
                    ->formatStateUsing(function ($state, $record) {
                        // Saat view/edit, tampilkan masked untuk non-admin
                        if ($record && shouldMaskData()) {
                            return maskSensitiveData($state, 10, 0);
                        }
                        return $state;
                    })
                    ->disabled(fn ($record) => $record && shouldMaskData()), // Non-admin can't edit
                Textarea::make('xendit_response')
                    ->columnSpanFull(),
                TextInput::make('account_number')
                    ->formatStateUsing(function ($state, $record) {
                        // Saat view/edit, tampilkan masked untuk non-admin
                        if ($record && shouldMaskData()) {
                            return maskAccountNumber($state);
                        }
                        return $state;
                    })
                    ->dehydrateStateUsing(fn ($state) => $state) // Save original value
                    ->disabled(fn ($record) => $record && shouldMaskData()), // Non-admin can't edit
                DateTimePicker::make('paid_at'),
                DateTimePicker::make('expired_at'),
                Textarea::make('payment_proof')
                    ->columnSpanFull(),
                DateTimePicker::make('refunded_at'),
                TextInput::make('refund_amount')
                    ->numeric(),
                Textarea::make('refund_reason')
                    ->columnSpanFull(),
            ]);
    }
}
