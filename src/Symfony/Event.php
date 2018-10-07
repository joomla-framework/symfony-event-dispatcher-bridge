<?php
/**
 * Part of the Joomla Framework Symfony Event Dispatcher Bridge
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\SymfonyEventDispatcherBridge\Symfony;

use Joomla\Event\EventInterface;
use Symfony\Component\EventDispatcher\Event as SymfonyEvent;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Bridge class decorating the Symfony Event class with the Joomla EventInterface
 *
 * @since  __DEPLOY_VERSION__
 */
class Event extends SymfonyEvent implements EventInterface
{
	/**
	 * The decorated event.
	 *
	 * @var    SymfonyEvent
	 * @since  __DEPLOY_VERSION__
	 */
	private $event;

	/**
	 * The event name.
	 *
	 * @var    string
	 * @since  1.0
	 */
	private $name;

	/**
	 * Constructor.
	 *
	 * @param   SymfonyEvent  $event  The event to decorate.
	 * @param   string        $name   The event name.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function __construct(SymfonyEvent $event, string $name = '')
	{
		$this->event = $event;

		// Symfony Event objects do not have the event name attached to them, so just use the event class name if one isn't provided
		$this->name = $name ?: get_class($event);
	}

	/**
	 * Magic method to proxy method calls to the decorated event.
	 *
	 * @param   string  $name       The method on the event to call.
	 * @param   array   $arguments  The arguments to pass to the event.
	 *
	 * @return  mixed   The result of the method call.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function __call($name, $arguments)
	{
		if (!method_exists($this->event, $name))
		{
			throw new \BadMethodCallException(
				sprintf('Call to undefined method %1$s on decorated event %2$s', $name, \get_class($this->event))
			);
		}

		return $this->event->$name(...$arguments);
	}

	/**
	 * Get an event argument value.
	 *
	 * @param   string  $name     The argument name.
	 * @param   mixed   $default  The default value if not found.
	 *
	 * @return  mixed  The argument value or the default value.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getArgument($name, $default = null)
	{
		// Check for a property on the event
		if (isset($this->getEvent()->$name))
		{
			return $this->getEvent()->$name;
		}

		// If a GenericEvent, use its API
		if ($this->getEvent() instanceof GenericEvent)
		{
			if ($this->getEvent()->hasArgument($name))
			{
				return $this->getEvent()->getArgument($name);
			}

			return $default;
		}

		$methods = get_class_methods($this->getEvent());

		sort($methods);
		$lcMethods = array_map('strtolower', $methods);

		// Check for a getter for the argument
		$methodName = 'get' . strtolower($name);

		if (\in_array($methodName, $lcMethods, true))
		{
			return $this->getEvent()->$methodName();
		}

		// Check for a isser for the argument
		$methodName = 'is' . strtolower($name);

		if (\in_array($methodName, $lcMethods, true))
		{
			return $this->getEvent()->$methodName();
		}

		// Check for a hasser for the argument
		$methodName = 'has' . strtolower($name);

		if (\in_array($methodName, $lcMethods, true))
		{
			return $this->getEvent()->$methodName();
		}

		// We did our best.
		return $default;
	}

	/**
	 * Get the decorated event.
	 *
	 * @return  SymfonyEvent
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getEvent(): SymfonyEvent
	{
		return $this->event;
	}

	/**
	 * Get the event name.
	 *
	 * @return  string  The event name.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Tell if the event propagation is stopped.
	 *
	 * @return  boolean  True if stopped, false otherwise.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function isStopped()
	{
		return $this->getEvent()->isPropagationStopped();
	}

	/**
	 * Stops the propagation of the event to further event listeners.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function stopPropagation()
	{
		$this->getEvent()->stopPropagation();
	}
}
