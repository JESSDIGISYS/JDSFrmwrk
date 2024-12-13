<?php

namespace JDS\Http\Middleware;

use JDS\Http\RedirectResponse;
use JDS\Http\Request;
use JDS\Http\Response;
use JDS\Session\SessionInterface;

class Authenticate implements MiddlewareInterface
{
	public function __construct(
		private SessionInterface $session
	)
	{
	}

	public function process(Request $request, RequestHandlerInterface $requestHandler): Response
	{
        $this->session->start();
        // first check to see if the user logged in is actually in the user table
        $sql = "SELECT
                user_id 
            FROM 
                users 
            WHERE 
                user_id = :user_id; ";
        $stmt = $requestHandler->getContainer()->get('connection')->prepare($sql);
        $stmt->bindValue(':user_id', $this->session->get('user_id'));
        $stmt->executeQuery();
        $user = $stmt->fetchAssociative();
        if (!$user) {
            $this->session->remove('auth_id');
        }

		if (!$this->session->isAuthenticated()) {
			$this->session->setFlash('error', 'Please sign in first!');

			return new RedirectResponse($requestHandler->getContainer()->get('routePath') . '/login');
		}

		return $requestHandler->handle($request);
	}
}


