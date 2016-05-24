<?php

namespace Admin\FrameworkBundle\Controller;

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
    	dump(__DIR__);
    	exit;
        $routes = $this->resolveUserRoutes($this->getUserRoutes());
        return $this->render(
        	'AdminFrameworkBundle:Default:index.html.twig',
        	array('userRoutes'=>$routes,'config'=>$this->getSystemConfig()));
    }
}
