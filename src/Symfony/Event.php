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

/**
 * Bridge class decorating the Symfony Event class with the Joomla EventInterface
 *
 * @since  __DEPLOY_VERSION__
 */
class Event implements EventInterface
{
	/**
	 * The decorated event.
	 *
	 * @var    SymfonyEvent
	 * @since  __DEPLOY_VERSION__
	 */
	private $event;

	/**
	 * Constructor.
	 *
	 * @param   SymfonyEvent  $event  The event to decorate.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function __construct(SymfonyEvent $event)
	{
		$this->event = $event;
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
		throw new \RuntimeException(
			sprintf(
				'Neither the property "%1$s" nor a method with public read access (get/has/is) exists in class "%2$s".',
				$name,
				\get_class($this->getEvent())
			)
		);
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
		// Symfony Event objects do not have the event name attached to them, so just use the event class name
		return \get_class($this->getEvent());
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
