<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

use Modules\FcmCentral\Events\UserGeneratedEvent;
use Modules\FcmCentral\Models\StoredEvent;

use Modules\FcmCommun\DataObjects\Enums\ObjectClasses;
use Modules\FcmCommun\DataObjects\Enums\UserGeneratedEventTypes;

uses(RefreshDatabase::class);
uses(Tests\TestCase::class);

pest()->group("Fcm Central");

beforeEach(function () {
});

it('creates a UserGeneratedEvent and stores it', function() {
    $this->assertTrue(StoredEvent::first() == null);

    $event = new UserGeneratedEvent(UserGeneratedEventTypes::MARIN_EN_FCM, "user_uuid", ObjectClasses::MARIN, "marin_uuid", []);
    StoredEvent::createFromUserGeneratedEvent($event);

    $this->assertTrue(StoredEvent::first() != null);
});

