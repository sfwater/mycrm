<?php

namespace Admin\FrameworkBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
* @Route("/admin")
*/
class DefaultController extends Controller
{
    /**
     * @Route("/")
     */
    public function indexAction()
    {
    	$router = $this->get("router");
    	$allRoutes = $router->getRouteCollection();
    	$userRoutes = $allRoutes->all();
    	dump($userRoutes);
    	exit;
        return $this->render('AdminFrameworkBundle:Default:index.html.twig',array("userRoutes"=>$userRoutes));
    }
}
