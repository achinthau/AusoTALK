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
                        $query = User::query()
                            ->where(function ($query) {
                                $query->whereNull('primary_extension')
                                    ->orWhereNull('secondary_extension');
                            });

                        // If company admin, filter by their company
                        $user = auth()->user();
                        if ($user?->company_id && ! $user->hasRole('super_admin')) {
                            $query->where('company_id', $user->company_id);
                        }

                        return $query
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

                        // Get all SIP/PJSIP extensions with status == 0 (unassigned) or already assigned to this user
                        return Extension::where('company_id', $company_id)
                            ->whereHas('extensionType', function ($query) {
                                $query->whereIn('name', ['sip', 'pjsip', 'SIP', 'PJSIP']);
                            })
                            ->where(function ($query) use ($user) {
                                $query->where('status', 0);
                                if ($user && $user->primary_extension) {
                                    $query->orWhere('number', $user->primary_extension);
                                }
                            })
                            ->get()
                            ->mapWithKeys(fn ($ext) => [
                                $ext->number => $ext->number,
                            ])
                            ->toArray();
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

                        // Get all IAX/IAX2 extensions with status == 0 (unassigned) or already assigned to this user
                        return Extension::where('company_id', $company_id)
                            ->whereHas('extensionType', function ($query) {
                                $query->whereIn('name', ['iax', 'iax2', 'IAX', 'IAX2']);
                            })
                            ->where(function ($query) use ($user) {
                                $query->where('status', 0);
                                if ($user && $user->secondary_extension) {
                                    $query->orWhere('number', $user->secondary_extension);
                                }
                            })
                            ->get()
                            ->mapWithKeys(fn ($ext) => [
                                $ext->number => $ext->number,
                            ])
                            ->toArray();
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

                // Update extension status to 1 (assigned)
                if ($data['primary_extension']) {
                    Extension::where('number', $data['primary_extension'])
                        ->where('company_id', $user->company_id)
                        ->update(['status' => 1]);
                }

                if ($data['secondary_extension']) {
                    Extension::where('number', $data['secondary_extension'])
                        ->where('company_id', $user->company_id)
                        ->update(['status' => 1]);
                }

                Notification::make()
                    ->success()
                    ->title('Extensions Assigned')
                    ->body('Extensions assigned to '.$user->name.' successfully.')
                    ->send();
            });
    }
}
