<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\UserResource\Actions;

use Closure;
use Filament\Actions\Concerns\CanCustomizeProcess;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Support\Enums\MaxWidth;
use Filament\Support\Services\RelationshipJoiner;
use Filament\Tables\Table;
use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Arr;

use function Filament\Support\generate_search_column_expression;
use function Filament\Support\generate_search_term_expression;

use Modules\FcmCommun\DataObjects\UserGeneratedEvent;

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

            $this->process(function () use ($data, $record, $relationship) {

                $parcours = $record;
                //ddd($parcours);
                $user = $relationship->getParent();

                $event = new UserGeneratedEvent(
                    event_type: "parcours_attribue",
                    user_id: auth()->user()->uuid,
                    object_class: get_class($user),
                    object_uuid: $user->uuid,
                    detail: [
                        "parcoursserialise" => $parcours->id,
                        // "parcours" => $parcours->uuid,
                        // "version" => $parcours->version,
                    ]
                );

                event($event);
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
