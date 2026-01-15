<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources;

use Modules\FcmCentral\Filament\Fcmcentral\Resources\DocumentationResource\Pages;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\DocumentationResource\RelationManagers;
use Modules\FcmCentral\Models\Documentation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;



 
use Modules\FcmCentral\Helpers\OutilsHelper;

class DocumentationResource extends Resource
{
    protected static ?string $model = Documentation::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function canAccess(array $parameters = []): bool
    {
        return false; // TODO: déterminer ce qu'on doit faire avec ces pages. C'est quoi une documentation ?
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                
                    TextInput::make('titre')
                        ->required()
                        ->maxLength(150)
                        ->label('Titre'),
                 
                    TextInput::make('code')
                        ->label('Code')
                        ->maxLength(150),  
                        
                    Select::make('droits')  
                    ->label('Droits (ajoute permissions')  
                    ->multiple()
                    ->options([
                        'fcmcentral::droits.admin' => 'Admin',
                        'fcmcentral::droits.superviseur' => 'Superviseur',
                        'fcmcentral::droits.user' => 'User'
                    ])
                    ->helperText('Ajoute des permissions pour certaines docs '),
                    Select::make('color')
                        ->options(OutilsHelper::selectColors())
                        ->label('Couleur du Badge'),   
                        
                       MarkdownEditor::make('contenu')
                        ->required()
                        ->columnSpanFull()
                        ->helperText('creer une documentation pour les fonctions utiles de ffast')
                        ->label('Contenu (Readme, Markdown)'),
               
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
        ->defaultSort('ordre', 'asc')
            ->reorderable('ordre')
            ->defaultPaginationPageOption(25) 
            ->columns([
                
                        TextColumn::make('id')
                            ->label('ID')
                            ->toggleable(isToggledHiddenByDefault: true),

                        TextColumn::make('code')
                            ->label('Code')
                            ->sortable()
                            ->searchable()
                            ->badge()
                            ->color(fn($record)=>$record->color ?? 'info') 
                            ,  
                           
                        TextColumn::make('titre')
                            ->label('titre')
                            ->limit(50)
                            ->sortable()
                            ->searchable()
                            ->description(fn ($record) => substr($record->titre,0,40).'...')
            ])
            
    
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
              
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
                
            // ...other bulk actions...
        ]);

    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDocumentations::route('/'),
            'create' => Pages\CreateDocumentation::route('/create'),
            'edit' => Pages\EditDocumentation::route('/{record}/edit'),
            'view'   => Pages\ViewDocumentation::route('/{record}'), 
        ];
    }
}
