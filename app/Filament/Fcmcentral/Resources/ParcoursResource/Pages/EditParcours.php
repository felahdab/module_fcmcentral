<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\ParcoursResource\Pages;

use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Schema;
use Modules\FcmCentral\Events\UserGeneratedEvent;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\ParcoursResource;
use Modules\FcmCentral\Models\ParcoursType;
use Modules\FcmCentral\Services\ParcoursService;
use Modules\FcmCommun\DataObjects\Enums\ObjectClasses;
use Modules\FcmCommun\DataObjects\Enums\UserGeneratedEventTypes;

class EditParcours extends EditRecord
{
    protected static string $resource = ParcoursResource::class;
    protected static ?string $title = 'Modifier parcours';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                TextInput::make('code')->label('Code')->required()->maxLength(150),
                TextInput::make('libelle_court')->label('Libellé court')->required()->maxLength(150),
                TextInput::make('libelle_long')->label('Libellé long')->required()->maxLength(1500),
                Select::make('parcours_type_id')
                    ->label('Type de parcours')
                    ->options(ParcoursType::orderBy('libelle_court')->pluck('libelle_court', 'id'))
                    ->required()
                    ->searchable(),
                FileUpload::make('livret_pdf')
                    ->label('Livret PDF')
                    ->acceptedFileTypes(['application/pdf'])
                    ->disk('s3-public')
                    ->directory('fcmcentral/livretmentors/')
                    ->visibility('public')
                    ->downloadable()
                    ->openable()
                    ->columnSpanFull(),
            ])
        ;
    }

    protected function getHeaderActions(): array
    {
        $parcours_service = new ParcoursService();

        return [
            Action::make('view')
                ->label('Voir le parcours')
                ->icon('heroicon-m-queue-list')
                ->color('info')
                ->url(fn () => ParcoursResource::getUrl('view', ['record' => $this->record])),

            Action::make('figer')
                ->requiresConfirmation()
                ->visible(fn () => auth()->user()->can('fcmcentral::parcours.figer'))
                ->modalHeading(fn ($record) => 'Figer : '.$record->libelle_court)
                ->modalDescription('Action irréversible.')
                ->schema([
                    TextInput::make('version')->label('Version')->numeric()->required()->disabled()->dehydrated(),
                    DatePicker::make('date_de_debut')->label('Date de début')
                        ->minDate(Carbon::now()->today()->max($parcours_service->firstPossibleNewVersionDate($this->getRecord()))),
                ])
                ->fillForm(fn ($record) => [
                    'date_de_debut' => Carbon::now()->tomorrow()->max($parcours_service->firstPossibleNewVersionDate($this->getRecord())),
                    'version' => $record->getLatestSerializedVersion() + 1,
                ])
                ->action(fn (array $data, $record) => event(new UserGeneratedEvent(
                    UserGeneratedEventTypes::CREATION_PARCOURS_SERIALISE,
                    auth()->user()->uuid,
                    ObjectClasses::PARCOURS,
                    $record->uuid,
                    ['date_de_debut' => $data['date_de_debut'], 'version' => $data['version']]
                ))),
        ];
    }
}


