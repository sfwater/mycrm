<?php

namespace Admin\AccessControlBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Admin\AdminAclController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
/**
* @Route("/admin/acls")
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

    /**
     * 查询用户、用户组的权限
     * @Route(
     *      "/privileges/", name="admin_acls_privileges",
     *      options = {"name":"权限列表","description":"查看用户、用户组权限列表","category":"访问控制","order":2}
     *   )
     */
    public function privilegesAction(Request $request)
    {
        $username = $request->request->get('username');
        $groupname = $request->request->get('groupname');
        $em = $this->getDoctrine()->getManager();




        //如果是查询用户的权限
        if( !empty($username) ){
            $dql = 'SELECT dist FROM AdminUserBundle:User dist WHERE dist.id=:id OR dist.username LIKE :name OR dist.nickname   LIKE :name';
            $query = $em->createQuery($dql)->setParameters(array('id'=>intval($username),'name'=>"%$username%"));
            $one = $query->getOneOrNullResult();

            if( !$one ){
                throw new \Exception($username);
            }
            $result = $em->getRepository('AdminAccessControlBundle:PagePrivilege')->findByUserId($one->getId());

            return $this->jsonResponse(array('type'=>'user','privileges'=>$result));
        }
        else if( !empty($groupname) ){
            $dql = 'SELECT dist FROM AdminUserBundle:Role dist WHERE dist.name LIKE :name OR dist.role LIKE :name';
            $query = $em->createQuery($dql)->setParameters(array('name'=>"%$groupname%"));
            $one = $query->getOneOrNullResult();

            if( !$one ){
                throw new \Exception("group $groupname not found.");
            }
            $result = $em->getRepository('AdminAccessControlBundle:PagePrivilege')->findByGroupId($one->getId());

            return $this->jsonResponse(array('type'=>'gorup','privileges'=>$result));
        }
        return $this->jsonResponse(array());
    }
    /**
     * 保存用户、用户组的权限
     * @Route(
     *      "/privileges/", name="admin_acls_privileges_save",
     *      options = {"name":"保存权限","description":"保存用户、用户组权限列表","category":"访问控制","order":3}
     *   )
     */
    public function privilegesSaveAction(Request $request)
    {
        return $this->success();
    }
}
