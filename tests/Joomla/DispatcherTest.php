<?php
/**
 * Part of the Joomla Framework Symfony Event Dispatcher Bridge
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\SymfonyEventDispatcherBridge\Tests\Joomla;

use Joomla\Event\Dispatcher as JoomlaDispatcher;
use Joomla\Event\EventInterface;
use Joomla\Event\SubscriberInterface as JoomlaSubscriber;
use Joomla\SymfonyEventDispatcherBridge\Joomla\Dispatcher;
use Joomla\SymfonyEventDispatcherBridge\Joomla\Event;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\Event as SymfonyEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface as SymfonySubscriber;

/**
 * Test class for Joomla\SymfonyEventDispatcherBridge\Joomla\Dispatcher
 */
class DispatcherTest extends TestCase
{
	/**
	 * @testdox  Listeners are added to the decorated dispatcher
	 */
	public function testListenersAreAddedToTheDecoratedDispatcher()
	{
		$decoratedDispatcher = new JoomlaDispatcher;

		$this->assertTrue((new Dispatcher($decoratedDispatcher))->addListener('testEvent', 'strlen'));
		$this->assertTrue($decoratedDispatcher->hasListener('strlen', 'testEvent'));
	}

	/**
	 * @testdox  A Joomla event subscriber is added to the decorated dispatcher
	 */
	public function testAJoomlaEventSubscriberIsAddedToTheDecoratedDispatcher()
	{
		$decoratedDispatcher = new JoomlaDispatcher;

		$subscriber = new class implements JoomlaSubscriber, SymfonySubscriber
		{
			public static function getSubscribedEvents(): array
			{
				return [
					'testEvent' => 'handleTestEvent',
				];
			}

			public function handleTestEvent()
			{
				// Hello!
			}
		};

		(new Dispatcher($decoratedDispatcher))->addSubscriber($subscriber);

		$this->assertCount(1, $decoratedDispatcher->getListeners('testEvent'));
	}

	/**
	 * @testdox  A Symfony event subscriber is added to the decorated dispatcher
	 */
	public function testASymfonyEventSubscriberIsAddedToTheDecoratedDispatcher()
	{
		$decoratedDispatcher = new JoomlaDispatcher;

		$subscriber = new class implements SymfonySubscriber
		{
			public static function getSubscribedEvents(): array
			{
				return [
					'testEvent' => 'handleTestEvent',
				];
			}

			public function handleTestEvent()
			{
				// Hello!
			}
		};

		(new Dispatcher($decoratedDispatcher))->addSubscriber($subscriber);

		$this->assertCount(1, $decoratedDispatcher->getListeners('testEvent'));
	}

	/**
	 * @testdox  A Joomla event is dispatched
	 */
	public function testAJoomlaEventIsDispatched()
	{
		$decoratedDispatcher = new JoomlaDispatcher;

		$subscriber = new class implements SymfonySubscriber
		{
			public $isCalled = false;

			public static function getSubscribedEvents(): array
			{
				return [
					'testEvent' => 'handleTestEvent',
				];
			}

			public function handleTestEvent()
			{
				$this->isCalled = true;
			}
		};

		$event = new class('testEvent') extends SymfonyEvent implements EventInterface
		{
			private $name;

			public function __construct(string $name)
			{
				$this->name = $name;
			}

			public function getArgument($name, $default = null)
			{
				// Stub for interface completion
			}

			public function getName()
			{
				return $this->name;
			}

			public function isStopped()
			{
				// Stub for interface completion
			}
		};

		$dispatcher = new Dispatcher($decoratedDispatcher);
		$dispatcher->addSubscriber($subscriber);

		$this->assertSame($event, $dispatcher->dispatch($event->getName(), $event));

		$this->assertTrue($subscriber->isCalled);
	}

	/**
	 * @testdox  A decorated event is dispatched
	 */
	public function testADecoratedEventIsDispatched()
	{
		$subscriber = new class implements SymfonySubscriber
		{
			public $isCalled = false;

			public static function getSubscribedEvents(): array
			{
				return [
					'testEvent' => 'handleTestEvent',
				];
			}

			public function handleTestEvent()
			{
				$this->isCalled = true;
			}
		};

		$decoratedEvent = new class('testEvent') implements EventInterface
		{
			private $name;

			public function __construct(string $name)
			{
				$this->name = $name;
			}

			public function getArgument($name, $default = null)
			{
				// Stub for interface completion
			}

			public function getName()
			{
				return $this->name;
			}

			public function isStopped()
			{
				// Stub for interface completion
			}

			public function stopPropagation()
			{
				// Stub for interface completion
			}
		};

		$event = new Event($decoratedEvent);

		$decoratedDispatcher = new JoomlaDispatcher;

		$dispatcher = new Dispatcher($decoratedDispatcher);
		$dispatcher->addSubscriber($subscriber);

		$this->assertSame($event, $dispatcher->dispatch('testEvent', $event));

		$this->assertTrue($subscriber->isCalled);
	}

	/**
	 * @testdox  A non-decorated Symfony event is dispatched
	 */
	public function testANonDecoratedSymfonyEventIsDispatched()
	{
		$subscriber = new class implements SymfonySubscriber
		{
			public $isCalled = false;

			public static function getSubscribedEvents(): array
			{
				return [
					'testEvent' => 'handleTestEvent',
				];
			}

			public function handleTestEvent()
			{
				$this->isCalled = true;
			}
		};

		$event = new SymfonyEvent;

		$decoratedDispatcher = new JoomlaDispatcher;

		$dispatcher = new Dispatcher($decoratedDispatcher);
		$dispatcher->addSubscriber($subscriber);

		$this->assertSame($event, $dispatcher->dispatch('testEvent', $event));

		$this->assertTrue($subscriber->isCalled);
	}

	/**
	 * @testdox  The registered listeners for an event are returned
	 */
	public function testRegisteredListenersForAnEventAreReturned()
	{
		$decoratedDispatcher = new JoomlaDispatcher;

		$subscriber = new class implements SymfonySubscriber
		{
			public static function getSubscribedEvents(): array
			{
				return [
					'testEvent' => 'handleTestEvent',
				];
			}

			public function handleTestEvent()
			{
				// Hello!
			}
		};

		$dispatcher = new Dispatcher($decoratedDispatcher);
		$dispatcher->addSubscriber($subscriber);

		$this->assertCount(1, $dispatcher->getListeners('testEvent'));
	}

	/**
	 * @testdox  The decorated dispatcher reports whether its internal dispatcher has listeners for an event
	 */
	public function testTheDecoratedDispatcherReportsWhetherItsInternalDispatcherHasListenersForAnEvent()
	{
		$decoratedDispatcher = new JoomlaDispatcher;

		$subscriber = new class implements SymfonySubscriber
		{
			public static function getSubscribedEvents(): array
			{
				return [
					'testEvent' => 'handleTestEvent',
				];
			}

			public function handleTestEvent()
			{
				// Hello!
			}
		};

		$dispatcher = new Dispatcher($decoratedDispatcher);
		$dispatcher->addSubscriber($subscriber);

		$this->assertTrue($dispatcher->hasListeners('testEvent'));
	}

	/**
	 * @testdox  Listeners are removed from the decorated dispatcher
	 */
	public function testListenersAreRemovedToTheDecoratedDispatcher()
	{
		$decoratedDispatcher = new JoomlaDispatcher;

		$dispatcher = new Dispatcher($decoratedDispatcher);

		$this->assertTrue($dispatcher->addListener('testEvent', 'strlen'));

		$dispatcher->removeListener('testEvent', 'strlen');

		$this->assertFalse($decoratedDispatcher->hasListener('strlen', 'testEvent'));
	}

	/**
	 * @testdox  A Joomla event subscriber is removed from the decorated dispatcher
	 */
	public function testAJoomlaEventSubscriberIsRemovedFromTheDecoratedDispatcher()
	{
		$decoratedDispatcher = new JoomlaDispatcher;

		$subscriber = new class implements JoomlaSubscriber, SymfonySubscriber
		{
			public static function getSubscribedEvents(): array
			{
				return [
					'testEvent' => 'handleTestEvent',
				];
			}

			public function handleTestEvent()
			{
				// Hello!
			}
		};

		$dispatcher = new Dispatcher($decoratedDispatcher);
		$dispatcher->addSubscriber($subscriber);

		$this->assertCount(1, $decoratedDispatcher->getListeners('testEvent'));

		$dispatcher->removeSubscriber($subscriber);

		$this->assertCount(0, $decoratedDispatcher->getListeners('testEvent'));
	}

	/**
	 * @testdox  A Symfony event subscriber is removed from the decorated dispatcher
	 */
	public function testASymfonyEventSubscriberIsRemovedFromTheDecoratedDispatcher()
	{
		$decoratedDispatcher = new JoomlaDispatcher;

		$subscriber = new class implements SymfonySubscriber
		{
			public static function getSubscribedEvents(): array
			{
				return [
					'testEvent' => 'handleTestEvent',
				];
			}

			public function handleTestEvent()
			{
				// Hello!
			}
		};

		$dispatcher = new Dispatcher($decoratedDispatcher);
		$dispatcher->addSubscriber($subscriber);

		$this->assertCount(1, $decoratedDispatcher->getListeners('testEvent'));

		$dispatcher->removeSubscriber($subscriber);

		$this->assertCount(0, $decoratedDispatcher->getListeners('testEvent'));
	}
}
