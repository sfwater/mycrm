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
     *      "/login", name="admin_login"
     *   )
     * @Template("AdminConsoleBundle:Default:login.html.twig")
     */
    public function loginAction()
    {
        return array();
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
