<?php

namespace App\Filament\Resources\Extensions\Schemas;

use App\Models\Company;
use App\Models\ExtensionType;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ExtensionForm
{
    public static function configure(Schema $schema): Schema
    {
        $userCompanyId = auth()->user()?->company_id;

        return $schema
            ->components([
                Select::make('company_id')
                    ->label('Company')
                    ->options(Company::pluck('name', 'id'))
                    ->required()
                    ->disabled(fn () => auth()->user()?->company_id !== null)
                    ->default(fn () => auth()->user()?->company_id)
                    ->live(),
                TextInput::make('number')
                    ->label('Extension Number')
                    ->placeholder('e.g., 1001')
                    ->required()
                    ->maxLength(4)
                    ->regex('/^\d{3,4}$/')
                    ->validationMessages([
                        'regex' => 'Extension number must be 3-4 digits.',
                    ]),
                Select::make('extension_type_id')
                    ->label('Extension Type')
                    ->options(fn ($get) => self::getExtensionTypeOptions($get, $userCompanyId))
                    ->required()
                    ->live()
                    ->preload()
                    ->searchable()
                    ->when(
                        auth()->user()?->hasRole('super_admin') === true,
                        function ($component) {
                            return $component
                                ->createOptionForm(fn ($form) => $form
                                    ->schema([
                                        TextInput::make('name')
                                            ->label('Type Name')
                                            ->placeholder('e.g., SIP, IAX2, PJSIP')
                                            ->required()
                                            ->maxLength(255)
                                            ->unique('extension_types', 'name'),
                                    ])
                                )
                                ->createOptionUsing(function ($data) {
                                    // Only super admins can create extension types
                                    if (! auth()->user()?->hasRole('super_admin')) {
                                        throw new \Exception('Only administrators can create extension types.');
                                    }

                                    return ExtensionType::create($data)->id;
                                });
                        }
                    ),
                TextInput::make('password')
                    ->label('Password')
                    ->password()
                    ->revealable()
                    ->nullable()
                    ->helperText('Leave blank to auto-generate a secure password')
                    ->dehydrated()
                    ->default(fn () => Str::random(16)),
                Hidden::make('context')
                    ->default(fn ($get) => self::getContextForCompany($get)),
                Hidden::make('status')
                    ->default('0'),
                Hidden::make('exten_type')
                    ->default(fn ($get) => self::getExtensionTypeName($get)),
                Hidden::make('updatedby')
                    ->default(fn () => auth()->user()?->name ?? 'ADMIN'),
            ]);
    }

    protected static function getContextForCompany($get)
    {
        $companyId = $get('company_id');
        if ($companyId) {
            $company = Company::find($companyId);

            return $company?->context ?? '';
        }

        return '';
    }

    protected static function getExtensionTypeName($get)
    {
        $typeId = $get('extension_type_id');
        if ($typeId) {
            $type = ExtensionType::find($typeId);

            return $type?->name ?? '';
        }

        return '';
    }

    protected static function getExtensionTypeOptions($get, $userCompanyId)
    {
        $companyId = $get('company_id') ?? $userCompanyId;

        if (! $companyId) {
            return ExtensionType::pluck('name', 'id')->toArray();
        }

        $company = Company::find($companyId);

        if ($company) {
            // Get extension types for this company with explicit table specification
            $extensionTypes = $company->extensionTypes()
                ->select('extension_types.id', 'extension_types.name')
                ->get()
                ->pluck('name', 'id')
                ->toArray();

            if (! empty($extensionTypes)) {
                return $extensionTypes;
            }
        }

        // Fallback to all extension types if none are allocated
        return ExtensionType::pluck('name', 'id')->toArray();
    }

    public static function configureEdit(Schema $schema): Schema
    {
        $userCompanyId = auth()->user()?->company_id;

        return $schema
            ->components([
                Select::make('company_id')
                    ->label('Company')
                    ->options(Company::pluck('name', 'id'))
                    ->required()
                    ->disabled(true),
                TextInput::make('number')
                    ->label('Extension Number')
                    ->placeholder('e.g., 1001')
                    ->required()
                    ->maxLength(4)
                    ->disabled(true)
                    ->regex('/^\d{3,4}$/')
                    ->validationMessages([
                        'regex' => 'Extension number must be 3-4 digits.',
                    ]),
                Select::make('extension_type_id')
                    ->label('Extension Type')
                    ->options(fn ($get) => self::getExtensionTypeOptions($get, $userCompanyId))
                    ->required()
                    ->preload()
                    ->searchable()
                    ->disabled(true),
                TextInput::make('password')
                    ->label('Password')
                    ->password()
                    ->revealable()
                    ->nullable()
                    ->helperText('Update to change password')
                    ->dehydrated(),
                Hidden::make('context')
                    ->default(fn ($get) => self::getContextForCompany($get)),
                Hidden::make('status')
                    ->default('0'),
                Hidden::make('exten_type')
                    ->default(fn ($get) => self::getExtensionTypeName($get)),
                Hidden::make('updatedby')
                    ->default(fn () => auth()->user()?->name ?? 'ADMIN'),
            ]);
    }
}
