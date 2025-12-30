<?php

namespace App\Filament\Resources\Companies\Pages;

use App\Filament\Resources\Companies\CompanyResource;
use App\Filament\Resources\Companies\Schemas\EditCompanyForm;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Schema;

class EditCompany extends EditRecord
{
    protected static string $resource = CompanyResource::class;

    public function form(Schema $schema): Schema
    {
        return $schema->components(EditCompanyForm::get());
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
