<?php

namespace Admin\DWZBackendBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ConsoleExceptionListener{

	private $translator;
	public function __construct($translator){
		$this->translator = $translator;
	}

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        // You get the exception object from the received event
        $exception = $event->getException();
        $message = $this->translator->trans($exception->getMessage());

        // Customize your response object to display the exception details
		$response = new Response();
		$response->setContent(json_encode(array(
			"status" => false,
			"statusCode" => 500,
			"message" => $message,
			"navTabId" => "",
			"rel" => "",
			"callbackType" => "",
			"forwardUrl" => "",
			"confirmMsg" => ""
		)));

        // HttpExceptionInterface is a special type of exception that
        // holds status code and header details
        // if ($exception instanceof HttpExceptionInterface) {
        //     $response->setStatusCode($exception->getStatusCode());
        //     $response->headers->replace($exception->getHeaders());
        // } else {
        //     $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
        // }

        //重新设置StatusCode
        $response->setStatusCode(200);
        $exception instanceof HttpExceptionInterface && $exception->setStatusCode(200);

		$response->headers->set('Content-Type', 'application/json');
        // Send the modified response object to the event
        $event->setResponse($response);
    }	
}

?>