<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources;

use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use App\Models\User;
use Modules\FcmCentral\Models\StoredEvent;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\StoredEventResource\Pages\ListStoredEvents;

class StoredEventResource extends Resource
{
    protected static ?string $model = StoredEvent::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static string|\UnitEnum|null $navigationGroup = 'Gestion';
    protected static ?string $navigationLabel = 'Journal des événements';
    protected static ?string $modelLabel = 'Événement';
    protected static ?string $pluralModelLabel = 'Journal des événements';
    protected static ?int $navigationSort = 99;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('d/m/Y H:i:s')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('event_type')
                    ->label('Type d\'événement')
                    ->badge()
                    ->sortable()
                    ->searchable()
                    ->formatStateUsing(fn ($state) => str($state)->afterLast('\\')->toString()),

                TextColumn::make('user_uuid')
                    ->label('Utilisateur')
                    ->sortable()
                    ->searchable()
                    ->formatStateUsing(function ($state) {
                        if (!$state) return '—';
                        $user = User::where('uuid', $state)->first();
                        return $user?->display_name ?? $user?->name ?? $state;
                    }),

                TextColumn::make('object_class')
                    ->label('Objet')
                    ->badge()
                    ->color('gray')
                    ->formatStateUsing(fn ($state) => str($state)->afterLast('\\')->toString())
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('object_uuid')
                    ->label('UUID objet')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->copyable()
                    ->limit(20),

                TextColumn::make('detail.type_element')
                    ->label('Élément')
                    ->badge()
                    ->color('info')
                    ->placeholder('—'),

                TextColumn::make('detail.uuid_element')
                    ->label('UUID élément')
                    ->limit(20)
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->placeholder('—'),

                TextColumn::make('detail.commentaire')
                    ->label('Commentaire')
                    ->limit(80)
                    ->wrap()
                    ->placeholder('—'),

                TextColumn::make('detail.date')
                    ->label('Date élément')
                    ->formatStateUsing(fn ($state) => $state
                        ? \Carbon\Carbon::parse($state)->format('d/m/Y H:i')
                        : '—'
                    )
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('event_type')
                    ->label('Type d\'événement')
                    ->options(
                        StoredEvent::query()
                            ->distinct()
                            ->pluck('event_type')
                            ->mapWithKeys(fn ($v) => [
                                $v => str($v)->afterLast('\\')->toString()
                            ])
                            ->toArray()
                    ),

                SelectFilter::make('user_uuid')
                    ->label('Utilisateur')
                    ->options(
                        User::query()
                            ->whereIn('uuid', StoredEvent::distinct()->pluck('user_uuid'))
                            ->get()
                            ->mapWithKeys(fn ($u) => [
                                $u->uuid => $u->display_name ?? $u->name
                            ])
                            ->toArray()
                    ),

                Filter::make('created_at')
                    ->label('Période')
                    ->form([
                        DatePicker::make('from')->label('Du'),
                        DatePicker::make('until')->label('Au'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        $query
                            ->when($data['from'], fn ($q) => $q->whereDate('created_at', '>=', $data['from']))
                            ->when($data['until'], fn ($q) => $q->whereDate('created_at', '<=', $data['until']));
                    }),
            ])
            ->searchable();
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStoredEvents::route('/'),
        ];
    }
}

