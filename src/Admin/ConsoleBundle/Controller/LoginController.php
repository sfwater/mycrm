<?php

namespace Admin\ConsoleBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Admin\AdminBaseController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;


class LoginController extends AdminBaseController
{
    /**
     * 登陆
     * @Route(
     *      "/login_check", name="admin_login_check"
     *   )
     */
    public function loginCheckAction()
    {
        return array();
    }

    /**
     * 登陆
     * @Route(
     *      "/login", name="admin_login"
     *   )
     * @Template("AdminConsoleBundle:Login:login.html.twig")
     */
    public function loginAction()
    {
        $configs = $this->container->getParameter('admin_console');
        return array('configs'=>$configs);
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
