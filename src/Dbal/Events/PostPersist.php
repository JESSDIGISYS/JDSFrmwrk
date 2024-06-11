<?php

namespace JDS\Dbal\Events;

use JDS\Dbal\Entity;
use JDS\EventDispatcher\Event;

class PostPersist extends Event
{
	public function __construct(private Entity $subject)
	{
	}
}