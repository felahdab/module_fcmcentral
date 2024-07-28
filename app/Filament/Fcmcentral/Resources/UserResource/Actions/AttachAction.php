<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\UserResource\Actions;

use Filament\Forms\Form;
use Filament\Support\Services\RelationshipJoiner;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\Relation;


use Modules\FcmCentral\Services\ParcoursService;

use Modules\FcmCentral\Events\UserGeneratedEvent;

use Filament\Tables\Actions\AttachAction as BaseAction;

class AttachAction extends BaseAction
{

    protected function setUp(): void
    {
        parent::setUp();

        $this->action(function (array $arguments, array $data, Form $form, Table $table): void {
            /** @var BelongsToMany $relationship */
            $relationship = Relation::noConstraints(fn () => $table->getRelationship());

            $relationshipQuery = app(RelationshipJoiner::class)->prepareQueryForNoConstraints($relationship);

            $isMultiple = is_array($data['recordId']);

            $record = $relationshipQuery
                ->{$isMultiple ? 'whereIn' : 'where'}($relationship->getQualifiedRelatedKeyName(), $data['recordId'])
                ->{$isMultiple ? 'get' : 'first'}();

            if ($record instanceof Model) {
                $this->record($record);
            }

            $this->process(function () use ($record, $relationship) {

                $parcours = $record;
                $user = $relationship->getParent();

                ParcoursService::attribuer_parcours_a_un_user($user, $parcours);
                
            }, []);

            if ($arguments['another'] ?? false) {
                $this->callAfter();
                $this->sendSuccessNotification();

                $this->record(null);

                $form->fill();

                $this->halt();

                return;
            }

            $this->success();
        });
    }

}
