<?php

namespace Admin\VerifyBundle\EventListener;


use Symfony\Component\Security\Core\Event\AuthenticationEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Admin\VerifyBundle\Exception\BadVerifycodeException;


class VerifycodeAuthenticationListener
{
	private $authKey = "authcode";

	public function __construct($options){
		if( !empty($options["authkey"]) ){
			$this->authKey = $options["authkey"];
		}
	}
	public function onPreAuthentication(AuthenticationEvent $event){
		$token = $event->getAuthenticationToken();
		if( $token ){
			$request = Request::createFromGlobals();
			$session = new Session();

			$authcode = $session->get($this->authKey);
			$postAuthcode = $request->request->get("vcode");

			if( empty($authcode) || $authcode !== $postAuthcode ){
				throw new BadVerifycodeException();
			}
		}
	}	
}
?>