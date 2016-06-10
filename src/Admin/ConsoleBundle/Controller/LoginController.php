<?php

namespace Admin\ConsoleBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Admin\AdminBaseController;
use Admin\AdminAclController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;


class LoginController extends AdminAclController
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
        $authenticationUtils = $this->get('security.authentication_utils');

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();
        return array('configs'=>$configs,
                'last_username' => $lastUsername,
                'error'         => $error,
            );
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
