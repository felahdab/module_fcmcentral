<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\FcmCentral\Events\UserGeneratedEvent;
use Modules\FcmCentral\Models\StoredEvent;
use Modules\FcmCommun\DataObjects\Enums\ObjectClasses;
use Modules\FcmCommun\DataObjects\Enums\UserGeneratedEventTypes;
use Tests\TestCase;

uses(RefreshDatabase::class);
uses(TestCase::class);

pest()->group('Fcm Central');

beforeEach(function () {});

it('creates a UserGeneratedEvent and stores it', function () {
    $this->assertTrue(null == StoredEvent::first());

    $event = new UserGeneratedEvent(UserGeneratedEventTypes::MARIN_EN_FCM, 'user_uuid', ObjectClasses::MARIN, 'marin_uuid', []);
    StoredEvent::createFromUserGeneratedEvent($event);

    $this->assertTrue(null != StoredEvent::first());
});
