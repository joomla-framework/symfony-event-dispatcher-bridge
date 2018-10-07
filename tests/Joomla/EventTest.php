<?php
/**
 * Part of the Joomla Framework Symfony Event Dispatcher Bridge
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\SymfonyEventDispatcherBridge\Tests\Joomla;

use Joomla\Event\Event as JoomlaEvent;
use Joomla\SymfonyEventDispatcherBridge\Joomla\Event;
use PHPUnit\Framework\TestCase;

/**
 * Test class for Joomla\SymfonyEventDispatcherBridge\Joomla\Event
 */
class EventTest extends TestCase
{
	/**
	 * @testdox  The decorated event is returned
	 */
	public function testDecoratedEventIsReturned()
	{
		$decoratedEvent = new JoomlaEvent('Bridge Test');

		$this->assertSame($decoratedEvent, (new Event($decoratedEvent))->getEvent());
	}

	/**
	 * @testdox  The propagation state is reported
	 */
	public function testThePropagationStateIsReported()
	{
		$decoratedEvent = new JoomlaEvent('Bridge Test');

		$this->assertFalse((new Event($decoratedEvent))->isPropagationStopped());
	}

	/**
	 * @testdox  Event propagation is stopped
	 */
	public function testEventPropagationIsStopped()
	{
		$decoratedEvent = new JoomlaEvent('Bridge Test');

		$bridgeEvent = new Event($decoratedEvent);
		$bridgeEvent->stopPropagation();

		$this->assertTrue((new Event($decoratedEvent))->isPropagationStopped());
	}
}
