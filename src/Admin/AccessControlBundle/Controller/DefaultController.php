<?php

namespace Admin\AccessControlBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Admin\AdminAclController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
/**
* @Route("/acls")
*/
class DefaultController extends AdminAclController
{
    /**
     * 所有访问控制列表
     * @Route(
     *      "/pages/", name="admin_acls_pages_index",
     *      options = {"name":"页面控制","description":"为用户或用户组分配页面访问权限","category":"访问控制","order":1, "show":true}
     *   )
     * @Method("GET") 
     * @Template("AdminAccessControlBundle:Default:index.html.twig")
     */
    public function indexAction()
    {
        $routes = $this->getUserRoutes();
        $routes = $this->resolveUserRoutes($routes);
        return array('routes'=>$routes);
    }
}
