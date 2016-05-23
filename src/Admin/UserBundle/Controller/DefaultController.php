<?php

namespace Admin\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Admin\AdminBaseController;

/**
* @Route("/users")
*/
class DefaultController extends AdminBaseController
{
    /**
     * 所有用户列表 
     * @Route(
     *      "/", name="users_index",
     *   )
     * @ParamConverter("post", options={"mapping": {"postSlug": "slug"}})
     * @Template("AdminUserBundle:Default:index.html.twig")
     */
    public function indexAction()
    {
    	$user = $this->getUser();


        return $this->datas;
    }
}
