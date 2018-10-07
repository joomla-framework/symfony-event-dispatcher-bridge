<?php
/**
 * Part of the Joomla Framework Symfony Event Dispatcher Bridge
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\SymfonyEventDispatcherBridge\Joomla;

use Joomla\Event\Dispatcher as JoomlaDispatcher;
use Joomla\Event\DispatcherInterface;
use Joomla\Event\EventInterface;
use Joomla\Event\SubscriberInterface;
use Joomla\SymfonyEventDispatcherBridge\Symfony\Event as SymfonyBridgeEvent;
use Symfony\Component\EventDispatcher\Event as SymfonyEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Bridge class decorating a Joomla DispatcherInterface implementation with the Symfony EventDispatcherInterface
 *
 * @since  __DEPLOY_VERSION__
 */
class Dispatcher implements EventDispatcherInterface
{
	/**
	 * The decorated dispatcher.
	 *
	 * @var    DispatcherInterface
	 * @since  __DEPLOY_VERSION__
	 */
	private $dispatcher;

	/**
	 * A container holding wrapped event subscribers
	 *
	 * @var    SubscriberInterface[]
	 * @since  __DEPLOY_VERSION__
	 */
	private $wrappedSubscribers = [];

	/**
	 * Constructor.
	 *
	 * @param   DispatcherInterface  $dispatcher  The decorated dispatcher.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function __construct(DispatcherInterface $dispatcher)
	{
		$this->dispatcher = $dispatcher;
	}

	/**
	 * Adds an event listener that listens on the specified events.
	 *
	 * @param   string    $eventName  The event to listen on
	 * @param   callable  $callback   The listener
	 * @param   integer   $priority   The higher this value, the earlier an event listener will be triggered in the chain (defaults to 0)
	 *
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function addListener($eventName, $callback, $priority = 0): bool
	{
		return $this->dispatcher->addListener($eventName, $callback, $priority);
	}

	/**
	 * Adds an event subscriber.
	 *
	 * @param   EventSubscriberInterface  $subscriber  The subscriber.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function addSubscriber(EventSubscriberInterface $subscriber)
	{
		if ($subscriber instanceof SubscriberInterface)
		{
			$this->dispatcher->addSubscriber($subscriber);

			return;
		}

		$this->dispatcher->addSubscriber($this->getWrappedSubscriber($subscriber));
	}

	/**
	 * Dispatches an event to all registered listeners.
	 *
	 * @param   string        $name   The name of the event to dispatch.
	 * @param   SymfonyEvent  $event  The event to pass to the event handlers/listeners
	 *
	 * @return  SymfonyEvent  The event after being passed through all listeners.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function dispatch($name, SymfonyEvent $event = null): SymfonyEvent
	{
		if ($event instanceof EventInterface)
		{
			$this->dispatcher->dispatch($name, $event);

			return $event;
		}

		if ($event instanceof Event)
		{
			$this->dispatcher->dispatch($name, $event->getEvent());

			return $event;
		}

		$decoratingEvent = new SymfonyBridgeEvent($event, $name);

		$this->dispatcher->dispatch($name, $decoratingEvent);

		return $event;
	}

	/**
	 * Gets the listener priority for a specific event.
	 *
	 * Returns null if the event or the listener does not exist.
	 *
	 * @param   string    $eventName  The name of the event
	 * @param   callable  $listener   The listener
	 *
	 * @return  integer|null The event listener priority
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getListenerPriority($eventName, $listener)
	{
		if (!($this->dispatcher instanceof JoomlaDispatcher))
		{
			throw new \RuntimeException(
				sprintf(
					'The `getListenerPriority` method is only implemented on subclasses of "%1$s".',
					JoomlaDispatcher::class
				)
			);
		}

		return $this->dispatcher->getListenerPriority($eventName, $listener);
	}

	/**
	 * Gets the listeners of a specific event or all listeners sorted by descending priority.
	 *
	 * @param   string  $event  The event to fetch listeners for
	 *
	 * @return  callable[]  An array of registered listeners sorted according to their priorities.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getListeners($event = null)
	{
		if ($event === null)
		{
			throw new \InvalidArgumentException(
				sprintf(
					'"%1$s" requires an event name to retrieve listeners for.',
					DispatcherInterface::class
				)
			);
		}

		return $this->dispatcher->getListeners($event);
	}

	/**
	 * Checks whether an event has any registered listeners.
	 *
	 * @param   string  $eventName  The name of the event
	 *
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function hasListeners($eventName = null)
	{
		if ($eventName === null)
		{
			return false;
		}

		return \count($this->getListeners($eventName)) > 0;
	}

	/**
	 * Removes an event listener from the specified events.
	 *
	 * @param   string    $eventName  The event to remove a listener from.
	 * @param   callable  $listener   The listener to remove.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function removeListener($eventName, $listener)
	{
		$this->dispatcher->removeListener($eventName, $listener);
	}

	/**
	 * Removes an event subscriber.
	 *
	 * @param   EventSubscriberInterface  $subscriber  The subscriber.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function removeSubscriber(EventSubscriberInterface $subscriber)
	{
		if ($subscriber instanceof SubscriberInterface)
		{
			$this->dispatcher->removeSubscriber($subscriber);

			return;
		}

		$this->dispatcher->removeSubscriber($this->getWrappedSubscriber($subscriber));
	}

	/**
	 * Create a wrapped event subscriber to proxy the Symfony implementation to the Joomla implementation
	 *
	 * @param   EventSubscriberInterface  $subscriber  The subscriber to wrap.
	 *
	 * @return  SubscriberInterface
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	private function getWrappedSubscriber(EventSubscriberInterface $subscriber): SubscriberInterface
	{
		$hash = spl_object_hash($subscriber);

		if (isset($this->wrappedSubscribers[$hash]))
		{
			return $this->wrappedSubscribers[$hash];
		}

		$wrappedSubscriber = new class($subscriber) implements SubscriberInterface
		{
			/**
			 * The subscriber being decorated.
			 *
			 * @var    EventSubscriberInterface
			 * @since  __DEPLOY_VERSION__
			 */
			private static $subscriber;

			/**
			 * Decorating subscriber constructor.
			 *
			 * @param   EventSubscriberInterface  $subscriber  The subscriber being decorated.
			 *
			 * @since   __DEPLOY_VERSION__
			 */
			public function __construct(EventSubscriberInterface $subscriber)
			{
				self::$subscriber = $subscriber;
			}

			/**
			 * Magic method to proxy subscriber method calls.
			 *
			 * @param   string  $name       The method on the subscriber to call.
			 * @param   array   $arguments  The arguments to pass to the subscriber.
			 *
			 * @return  mixed   The filtered input value.
			 *
			 * @since   __DEPLOY_VERSION__
			 */
			public function __call($name, $arguments)
			{
				if (self::$subscriber === null)
				{
					throw new \RuntimeException('The wrapped subscriber was not correctly initialised');
				}

				if (!method_exists(self::$subscriber, $name))
				{
					throw new \BadMethodCallException(
						sprintf('Call to undefined method %1$s on decorated dispatcher %2$s', $name, \get_class(self::$subscriber))
					);
				}

				self::$subscriber->$name(...$arguments);
			}

			/**
			 * Returns an array of event names this subscriber wants to listen to.
			 *
			 * @return  array
			 *
			 * @since   __DEPLOY_VERSION__
			 */
			public static function getSubscribedEvents(): array
			{
				if (self::$subscriber === null)
				{
					throw new \RuntimeException('The wrapped subscriber was not correctly initialised');
				}

				$subscribedEvents = [];

				foreach (self::$subscriber->getSubscribedEvents() as $eventName => $params)
				{
					if (\is_string($params))
					{
						$subscribedEvents[$eventName][] = $params;
					}
					elseif (\is_string($params[0]))
					{
						$subscribedEvents[$eventName][] = [$params[0], $params[1] ?? 0];
					}
					else
					{
						foreach ($params as $listener)
						{
							$subscribedEvents[$eventName][] = [$listener[0], $listener[1] ?? 0];
						}
					}
				}

				return $subscribedEvents;
			}
		};

		return $this->wrappedSubscribers[$hash] = $wrappedSubscriber;
	}
}
