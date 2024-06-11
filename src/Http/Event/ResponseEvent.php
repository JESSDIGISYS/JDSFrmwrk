<?php

namespace JDS\Framework\Http\Event;

use JDS\Framework\EventDispatcher\Event;
use JDS\Framework\Http\Request;
use JDS\Framework\Http\Response;

class ResponseEvent extends Event
{
	public function __construct(
		private Request $request,
		private Response  $response
	)
	{
	}

	public function getRequest(): Request
	{
		return $this->request;
	}

	public function getResponse(): Response
	{
		return $this->response;
	}

}


