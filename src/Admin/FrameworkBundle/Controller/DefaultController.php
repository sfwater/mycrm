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
    	dump($this->getUserRoutes());
    	exit;
        return $this->render('AdminFrameworkBundle:Default:index.html.twig',array("userRoutes"=>$userRoutes));
    }
}
