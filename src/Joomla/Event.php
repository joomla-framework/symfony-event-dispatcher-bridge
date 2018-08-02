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
class Event extends SymfonyEvent
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
