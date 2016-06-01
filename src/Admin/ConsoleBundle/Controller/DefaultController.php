<?php

namespace Admin\ConsoleBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Admin\AdminBaseController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
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
        $configs = $this->container->getParameter('admin_console');
        $routes = $this->resolveUserRoutes($this->getUserRoutes());
        return $this->render('AdminConsoleBundle:Default:index.html.twig', array(
            'userRoutes'=>$routes,
            'configs'=>$configs,
            ));
    }



    /**
     * 退出登陆
     * @Route(
     *      "/logout", name="admin_logout",
     *      options = {"name":"退出","description":"退出登陆","category":"console","order":9, "type":"console","show":true,"target":""} 
     *   )
     * @Method("GET")
     */
    public function logoutAction()
    {
    }

}
