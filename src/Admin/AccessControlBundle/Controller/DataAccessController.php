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
class DataAccessController extends AdminAclController
{
    /**
     * 所有访问控制列表
     * @Route(
     *      "/datas", name="admin_acls_datas_index",
     *      options = {"name":"数据控制","description":"建立用户关系实现数据访问控制","category":"访问控制","order":2, "show":true}
     *   )
     * @Method("GET") 
     * @Template("AdminAccessControlBundle:DataAccess:index.html.twig")
     */
    public function indexAction()
    {
        return array();
    }
}
