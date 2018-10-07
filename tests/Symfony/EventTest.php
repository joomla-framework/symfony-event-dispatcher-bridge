<?php
/**
 * Part of the Joomla Framework Symfony Event Dispatcher Bridge
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\SymfonyEventDispatcherBridge\Tests\Symfony;

use Joomla\SymfonyEventDispatcherBridge\Symfony\Event;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\Event as SymfonyEvent;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Test class for Joomla\SymfonyEventDispatcherBridge\Symfony\Event
 */
class EventTest extends TestCase
{
	/**
	 * @testdox  The decorated event is returned
	 */
	public function testDecoratedEventIsReturned()
	{
		$decoratedEvent = new SymfonyEvent;

		$this->assertSame($decoratedEvent, (new Event($decoratedEvent))->getEvent());
	}

	/**
	 * @testdox  The event name is the decorated event class name
	 */
	public function testEventNameIsTheDecoratedEventClassName()
	{
		$decoratedEvent = new SymfonyEvent;

		$this->assertSame(SymfonyEvent::class, (new Event($decoratedEvent))->getName());
	}

	/**
	 * @testdox  Event argument from properties is retrieved
	 */
	public function testEventArgumentFromPropertiesIsRetrieved()
	{
		$decoratedEvent = new class extends SymfonyEvent
		{
			public $testArg = 42;
		};

		$this->assertSame(42, (new Event($decoratedEvent))->getArgument('testArg'));
	}

	/**
	 * @testdox  Event argument from GenericEvent API is retrieved
	 */
	public function testEventArgumentFromGenericEventIsRetrieved()
	{
		$decoratedEvent = new GenericEvent(null, ['testArg' => 42]);

		$this->assertSame(42, (new Event($decoratedEvent))->getArgument('testArg'));
	}

	/**
	 * @testdox  Event argument from getter is retrieved
	 */
	public function testEventArgumentFromGetterIsRetrieved()
	{
		$decoratedEvent = new class extends SymfonyEvent
		{
			public function getTestArg(): int
			{
				return 42;
			}
		};

		$this->assertSame(42, (new Event($decoratedEvent))->getArgument('testArg'));
	}

	/**
	 * @testdox  Event argument from isser is retrieved
	 */
	public function testEventArgumentFromIsserIsRetrieved()
	{
		$decoratedEvent = new class extends SymfonyEvent
		{
			public function isTestArg(): int
			{
				return 42;
			}
		};

		$this->assertSame(42, (new Event($decoratedEvent))->getArgument('testArg'));
	}

	/**
	 * @testdox  Event argument from hasser is retrieved
	 */
	public function testEventArgumentFromHasserIsRetrieved()
	{
		$decoratedEvent = new class extends SymfonyEvent
		{
			public function hasTestArg(): int
			{
				return 42;
			}
		};

		$this->assertSame(42, (new Event($decoratedEvent))->getArgument('testArg'));
	}

	/**
	 * @testdox  Default value returned when argument is not found
	 */
	public function testDefaultValueReturnedWhenEventArgumentIsNotFound()
	{
		$decoratedEvent = new SymfonyEvent;

		$this->assertSame(42, (new Event($decoratedEvent))->getArgument('testArg', 42));
	}

	/**
	 * @testdox  The propagation state is reported
	 */
	public function testThePropagationStateIsReported()
	{
		$decoratedEvent = new SymfonyEvent;

		$this->assertFalse((new Event($decoratedEvent))->isStopped());
	}

	/**
	 * @testdox  Event propagation is stopped
	 */
	public function testEventPropagationIsStopped()
	{
		$decoratedEvent = new SymfonyEvent;

		$bridgeEvent = new Event($decoratedEvent);
		$bridgeEvent->stopPropagation();

		$this->assertTrue((new Event($decoratedEvent))->isStopped());
	}
}
