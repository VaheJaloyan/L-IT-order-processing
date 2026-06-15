<?php

namespace App\Tests\Service\Notification;

use App\Notification\NotificationEvent;
use App\Notification\NotificationEventType;
use App\Service\Notification\Contract\NotificationHandlerInterface;
use App\Service\Notification\Notifier;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(Notifier::class)]
class NotifierTest extends TestCase
{
    private NotificationEvent $event;

    protected function setUp(): void
    {
        $this->event = new NotificationEvent(NotificationEventType::OrderCreated, ['order_id' => 1]);
    }

    public function testHandlerThatSupportsEventReceivesHandle(): void
    {
        $handler = $this->mockHandler(supports: true);
        $handler->expects(self::once())->method('handle')->with($this->event);

        (new Notifier([$handler]))->notify($this->event);
    }

    public function testHandlerThatDoesNotSupportEventIsSkipped(): void
    {
        $handler = $this->mockHandler(supports: false);
        $handler->expects(self::never())->method('handle');

        (new Notifier([$handler]))->notify($this->event);
    }

    public function testOnlyMatchingHandlersAreCalledWhenMultipleRegistered(): void
    {
        $matching    = $this->mockHandler(supports: true);
        $nonMatching = $this->mockHandler(supports: false);

        $matching->expects(self::once())->method('handle');
        $nonMatching->expects(self::never())->method('handle');

        (new Notifier([$matching, $nonMatching]))->notify($this->event);
    }

    public function testNoHandlersRegisteredCompletesWithoutError(): void
    {
        $this->expectNotToPerformAssertions();

        (new Notifier([]))->notify($this->event);
    }

    public function testNoMatchingHandlersHandleIsNeverCalled(): void
    {
        $h1 = $this->mockHandler(supports: false);
        $h2 = $this->mockHandler(supports: false);

        $h1->expects(self::never())->method('handle');
        $h2->expects(self::never())->method('handle');

        (new Notifier([$h1, $h2]))->notify($this->event);
    }

    // --- helper ---

    private function mockHandler(bool $supports): MockObject&NotificationHandlerInterface
    {
        $handler = $this->createMock(NotificationHandlerInterface::class);
        $handler->method('supports')->willReturn($supports);
        return $handler;
    }
}
