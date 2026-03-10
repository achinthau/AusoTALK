<?php

namespace App\Filament\Resources\Users\Actions;

use App\Models\Extension;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;

class AssignExtensionsAction extends Action
{
    public static function getDefaultName(): string
    {
        return 'assign-extensions';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label('Assign Extensions')
            ->icon('heroicon-o-link')
            ->modalHeading('Assign Extensions to User')
            ->form([
                Select::make('user_id')
                    ->label('Select User')
                    ->options(function () {
                        return User::query()
                            ->where(function ($query) {
                                $query->whereNull('primary_extension')
                                    ->orWhereNull('secondary_extension');
                            })
                            ->get()
                            ->mapWithKeys(function ($user) {
                                $extensions = [];
                                if ($user->primary_extension) {
                                    $extensions[] = 'Primary: '.$user->primary_extension;
                                }
                                if ($user->secondary_extension) {
                                    $extensions[] = 'Secondary: '.$user->secondary_extension;
                                }
                                $label = $user->name.(count($extensions) > 0 ? ' ('.implode(', ', $extensions).')' : '');

                                return [$user->id => $label];
                            });
                    })
                    ->required()
                    ->searchable()
                    ->live()
                    ->afterStateUpdated(function ($state, $set) {
                        if ($state) {
                            $user = User::find($state);
                            if ($user && $user->primary_extension) {
                                $set('primary_extension', $user->primary_extension);
                            }
                            if ($user && $user->secondary_extension) {
                                $set('secondary_extension', $user->secondary_extension);
                            }
                        } else {
                            $set('primary_extension', null);
                            $set('secondary_extension', null);
                        }
                    }),
                Select::make('primary_extension')
                    ->label('Primary Extension')
                    ->options(function ($get) {
                        if (! $get('user_id')) {
                            return [];
                        }

                        $user = User::find($get('user_id'));
                        $company_id = $user?->company_id;

                        if (! $company_id) {
                            return [];
                        }

                        // Get all SIP/PJSIP extensions for the company
                        $allExtensions = Extension::where('company_id', $company_id)
                            ->whereHas('extensionType', function ($query) {
                                $query->whereIn('name', ['sip', 'pjsip', 'SIP', 'PJSIP']);
                            })
                            ->get()
                            ->mapWithKeys(fn ($ext) => [$ext->number => $ext->number]);

                        // Get assigned extensions (from both primary and secondary columns)
                        $assignedExtensions = User::query()
                            ->where('id', '!=', $get('user_id'))
                            ->pluck('primary_extension')
                            ->merge(
                                User::query()
                                    ->where('id', '!=', $get('user_id'))
                                    ->pluck('secondary_extension')
                            )
                            ->filter()
                            ->unique()
                            ->values();

                        // Remove assigned extensions
                        return $allExtensions->filter(function ($value, $key) use ($assignedExtensions) {
                            return ! $assignedExtensions->contains($key);
                        })->toArray();
                    })
                    ->disabled(fn ($get) => ! $get('user_id'))
                    ->searchable()
                    ->live(),
                Select::make('secondary_extension')
                    ->label('Secondary Extension')
                    ->options(function ($get) {
                        if (! $get('user_id')) {
                            return [];
                        }

                        $user = User::find($get('user_id'));
                        $company_id = $user?->company_id;

                        if (! $company_id) {
                            return [];
                        }

                        // Get all IAX/IAX2 extensions for the company
                        $allExtensions = Extension::where('company_id', $company_id)
                            ->whereHas('extensionType', function ($query) {
                                $query->whereIn('name', ['iax', 'iax2', 'IAX', 'IAX2']);
                            })
                            ->get()
                            ->mapWithKeys(fn ($ext) => [$ext->number => $ext->number]);

                        // Get assigned extensions (from both primary and secondary columns)
                        $assignedExtensions = User::query()
                            ->where('id', '!=', $get('user_id'))
                            ->pluck('primary_extension')
                            ->merge(
                                User::query()
                                    ->where('id', '!=', $get('user_id'))
                                    ->pluck('secondary_extension')
                            )
                            ->filter()
                            ->unique()
                            ->values();

                        // Remove assigned extensions and primary extension if already selected
                        return $allExtensions->filter(function ($value, $key) use ($assignedExtensions, $get) {
                            return ! $assignedExtensions->contains($key) && $key != $get('primary_extension');
                        })->toArray();
                    })
                    ->disabled(fn ($get) => ! $get('user_id'))
                    ->searchable()
                    ->live(),
            ])
            ->action(function (array $data) {
                $user = User::find($data['user_id']);

                $user->update([
                    'primary_extension' => $data['primary_extension'] ?? null,
                    'secondary_extension' => $data['secondary_extension'] ?? null,
                ]);

                Notification::make()
                    ->success()
                    ->title('Extensions Assigned')
                    ->body('Extensions assigned to '.$user->name.' successfully.')
                    ->send();
            });
    }
}
