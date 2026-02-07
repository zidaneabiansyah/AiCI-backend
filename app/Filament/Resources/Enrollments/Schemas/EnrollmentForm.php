<?php

namespace App\Filament\Resources\Enrollments\Schemas;

use App\Enums\EnrollmentStatus;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class EnrollmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Pendaftaran')
                    ->schema([
                        TextInput::make('enrollment_number')
                            ->label('Nomor Pendaftaran')
                            ->required()
                            ->disabled()
                            ->dehydrated()
                            ->default(fn () => 'ENR-' . date('Ymd') . '-' . str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT)),
                        
                        Select::make('user_id')
                            ->label('User')
                            ->relationship('user', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        
                        Select::make('class_id')
                            ->label('Kelas')
                            ->relationship('class', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        
                        Select::make('class_schedule_id')
                            ->label('Jadwal Kelas')
                            ->relationship('classSchedule', 'day_of_week')
                            ->searchable()
                            ->preload(),
                        
                        Select::make('status')
                            ->label('Status')
                            ->options(EnrollmentStatus::class)
                            ->default('pending')
                            ->required()
                            ->native(false),
                    ])
                    ->columns(2),
                
                Section::make('Data Siswa')
                    ->schema([
                        TextInput::make('student_name')
                            ->label('Nama Siswa')
                            ->required()
                            ->maxLength(255),
                        
                        TextInput::make('student_email')
                            ->label('Email Siswa')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->formatStateUsing(function ($state, $record) {
                                // Saat view/edit, tampilkan masked untuk non-admin
                                if ($record && shouldMaskData()) {
                                    return maskEmail($state);
                                }
                                return $state;
                            })
                            ->dehydrateStateUsing(fn ($state) => $state) // Save original value
                            ->disabled(fn ($record) => $record && shouldMaskData()), // Non-admin can't edit
                        
                        TextInput::make('student_phone')
                            ->label('No. Telepon Siswa')
                            ->tel()
                            ->required()
                            ->maxLength(20)
                            ->formatStateUsing(function ($state, $record) {
                                // Saat view/edit, tampilkan masked untuk non-admin
                                if ($record && shouldMaskData()) {
                                    return maskPhone($state);
                                }
                                return $state;
                            })
                            ->dehydrateStateUsing(fn ($state) => $state) // Save original value
                            ->disabled(fn ($record) => $record && shouldMaskData()), // Non-admin can't edit
                        
                        TextInput::make('student_age')
                            ->label('Usia Siswa')
                            ->numeric()
                            ->minValue(5)
                            ->maxValue(100)
                            ->suffix('tahun'),
                        
                        TextInput::make('student_grade')
                            ->label('Kelas/Tingkat')
                            ->maxLength(50),
                    ])
                    ->columns(2),
                
                Section::make('Data Orang Tua/Wali')
                    ->description('Wajib diisi untuk siswa di bawah 17 tahun')
                    ->schema([
                        TextInput::make('parent_name')
                            ->label('Nama Orang Tua/Wali')
                            ->maxLength(255),
                        
                        TextInput::make('parent_phone')
                            ->label('No. Telepon Orang Tua')
                            ->tel()
                            ->maxLength(20)
                            ->formatStateUsing(function ($state, $record) {
                                // Saat view/edit, tampilkan masked untuk non-admin
                                if ($record && shouldMaskData()) {
                                    return maskPhone($state);
                                }
                                return $state;
                            })
                            ->dehydrateStateUsing(fn ($state) => $state) // Save original value
                            ->disabled(fn ($record) => $record && shouldMaskData()), // Non-admin can't edit
                        
                        TextInput::make('parent_email')
                            ->label('Email Orang Tua')
                            ->email()
                            ->maxLength(255)
                            ->formatStateUsing(function ($state, $record) {
                                // Saat view/edit, tampilkan masked untuk non-admin
                                if ($record && shouldMaskData()) {
                                    return maskEmail($state);
                                }
                                return $state;
                            })
                            ->dehydrateStateUsing(fn ($state) => $state) // Save original value
                            ->disabled(fn ($record) => $record && shouldMaskData()), // Non-admin can't edit
                    ])
                    ->columns(3),
                
                Section::make('Informasi Tambahan')
                    ->schema([
                        Textarea::make('special_requirements')
                            ->label('Kebutuhan Khusus')
                            ->rows(3)
                            ->columnSpanFull(),
                        
                        Textarea::make('notes')
                            ->label('Catatan')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
                
                Section::make('Timeline')
                    ->schema([
                        Grid::make(4)
                            ->schema([
                                DateTimePicker::make('enrolled_at')
                                    ->label('Tanggal Daftar')
                                    ->default(now()),
                                
                                DateTimePicker::make('confirmed_at')
                                    ->label('Tanggal Konfirmasi'),
                                
                                DateTimePicker::make('cancelled_at')
                                    ->label('Tanggal Dibatalkan'),
                                
                                DateTimePicker::make('completed_at')
                                    ->label('Tanggal Selesai'),
                            ]),
                        
                        TextInput::make('cancellation_reason')
                            ->label('Alasan Pembatalan')
                            ->maxLength(500)
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),
            ]);
    }
}
