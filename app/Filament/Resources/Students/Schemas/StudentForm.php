<?php

namespace App\Filament\Resources\Students\Schemas;

use App\Models\Student;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class StudentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('event_id')
                    ->label('Acara')
                    ->relationship(
                        name: 'event',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn (Builder $query): Builder => $query->orderBy('name'),
                    )
                    ->searchable()
                    ->preload()
                    ->live()
                    ->required(),
                Select::make('class_id')
                    ->label('Kelas')
                    ->relationship(
                        name: 'eventClass',
                        titleAttribute: 'name',
                        modifyQueryUsing: function (Builder $query, Get $get): Builder {
                            if (blank($get('event_id'))) {
                                return $query->whereRaw('1 = 0');
                            }

                            return $query
                                ->where('event_id', $get('event_id'))
                                ->orderBy('sort_order')
                                ->orderBy('name');
                        },
                    )
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('name')
                    ->label('Nama Siswa')
                    ->required()
                    ->maxLength(255)
                    ->rule(function (Get $get): callable {
                        return function (string $attribute, mixed $value, \Closure $fail) use ($get): void {
                            $eventId = $get('event_id');
                            $classId = $get('class_id');
                            $name = mb_strtolower(trim((string) $value));
                            $motherName = mb_strtolower(trim((string) $get('mother_name')));

                            if (blank($eventId) || blank($classId) || $name === '' || $motherName === '') {
                                return;
                            }

                            if (\App\Models\Student::hasDuplicateIdentity(
                                eventId: $eventId,
                                classId: $classId,
                                name: $name,
                                motherName: $motherName,
                            )) {
                                $fail('Kombinasi nama siswa dan nama ibu kandung sudah terdaftar pada acara dan kelas ini.');
                            }
                        };
                    }),
                Select::make('gender')
                    ->options(Student::genderOptions())
                    ->label('Jenis Kelamin')
                    ->nullable(),
                TextInput::make('mother_name')
                    ->label('Nama Ibu Kandung')
                    ->required()
                    ->maxLength(255)
                    ->rule(function (Get $get): callable {
                        return function (string $attribute, mixed $value, \Closure $fail) use ($get): void {
                            $eventId = $get('event_id');
                            $classId = $get('class_id');
                            $name = mb_strtolower(trim((string) $get('name')));
                            $motherName = mb_strtolower(trim((string) $value));

                            if (blank($eventId) || blank($classId) || $name === '' || $motherName === '') {
                                return;
                            }

                            if (\App\Models\Student::hasDuplicateIdentity(
                                eventId: $eventId,
                                classId: $classId,
                                name: $name,
                                motherName: $motherName,
                            )) {
                                $fail('Kombinasi nama siswa dan nama ibu kandung sudah terdaftar pada acara dan kelas ini.');
                            }
                        };
                    }),
            ]);
    }
}
