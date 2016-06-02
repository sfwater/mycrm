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
     * 系统配置
     * @Route(
     *      "/config", name="admin_configuration",
     *      options = {"name":"系统配置","description":"修改系统配置","category":"console","order":7, "type":"console","show":true} 
     *   )
     * @Method("POST")
     */
    public function configAction()
    {
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

    /**
    * @Route("/test")
    */
    public function testAction(){
        $router = $this->get("router");
        $request = $this->get("request_stack")->getCurrentRequest();
        $route = $router->match($request->getPathInfo());
        dump($request);
        exit;
    }
}
