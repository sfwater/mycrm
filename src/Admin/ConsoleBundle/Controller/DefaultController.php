<?php

namespace Admin\ConsoleBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Admin\AdminBaseController;
/**
* @Route("/admin")
*/
class DefaultController extends AdminBaseController
{
    /**
     * @Route("/")
     */
    public function indexAction()
    {
        $routes = $this->resolveUserRoutes($this->getUserRoutes());
        return $this->render(
        	'AdminConsoleBundle:Default:index.html.twig',
        	array('userRoutes'=>$routes));
    }
}
