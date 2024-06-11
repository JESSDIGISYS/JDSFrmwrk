<?php

namespace JDS\Http\Event;

use JDS\EventDispatcher\Event;
use JDS\Http\Request;
use JDS\Http\Response;

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


