<?php
/**
 * Part of the Joomla Framework Symfony Event Dispatcher Bridge
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\SymfonyEventDispatcherBridge\Joomla;

use Joomla\Event\EventInterface;
use Symfony\Component\EventDispatcher\Event as SymfonyEvent;

/**
 * Bridge class decorating the Joomla EventInterface with the Symfony Event class
 *
 * @since  __DEPLOY_VERSION__
 */
class Event extends SymfonyEvent implements EventInterface
{
	/**
	 * The decorated event.
	 *
	 * @var    EventInterface
	 * @since  __DEPLOY_VERSION__
	 */
	private $event;

	/**
	 * Constructor.
	 *
	 * @param   EventInterface  $event  The event to decorate.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function __construct(EventInterface $event)
	{
		$this->event = $event;
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
		return $this->getEvent()->getArgument($name, $default);
	}

	/**
	 * Get the decorated event.
	 *
	 * @return  EventInterface
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getEvent(): EventInterface
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
		return $this->getEvent()->getName();
	}

	/**
	 * Returns whether further event listeners should be triggered.
	 *
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function isPropagationStopped()
	{
		return $this->getEvent()->isStopped();
	}

	/**
	 * Tell if the event propagation is stopped.
	 *
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function isStopped()
	{
		return $this->isPropagationStopped();
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
