<?php

namespace JDS\Dbal\Events;

use JDS\Framework\Dbal\Entity;
use JDS\Framework\EventDispatcher\Event;

class PostPersist extends Event
{
	public function __construct(private Entity $subject)
	{
	}
}