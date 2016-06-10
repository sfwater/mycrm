<?php

namespace Admin\AccessControlBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Admin\AdminAclController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Admin\AccessControlBundle\Entity\PagePrivilege;
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
     * @Method("POST")
     */
    public function privilegesAction(Request $request)
    {
        $username = $request->request->get('username');
        $groupname = $request->request->get('groupname');
        $em = $this->getDoctrine()->getManager();




        //如果是查询用户的权限
        if( !empty($username) ){
            $dql = 'SELECT dist FROM AdminUserBundle:User dist WHERE dist.id=:id OR dist.username=:name OR dist.nickname   =:name';
            $query = $em->createQuery($dql)->setParameters(array('id'=>intval($username),'name'=>$username));
            $one = $query->getOneOrNullResult();

            if( !$one ){
                throw new \Exception($username);
            }
            $result = $em->getRepository('AdminAccessControlBundle:PagePrivilege')->findByUserId($one->getId());

            $arr = array();
            foreach ($result as $key => $value) {
                $arr[] = $this->resolvePagePrivilege($value);
            }

            return $this->jsonResponse(array('type'=>'user','privileges'=>$arr));
        }
        else if( !empty($groupname) ){
            $dql = 'SELECT dist FROM AdminUserBundle:Role dist WHERE dist.name=:name OR dist.role=:name';
            $query = $em->createQuery($dql)->setParameters(array('name'=>$groupname));
            $one = $query->getOneOrNullResult();

            if( !$one ){
                throw new \Exception("group $groupname not found.");
            }
            $result = $em->getRepository('AdminAccessControlBundle:PagePrivilege')->findByGroupId($one->getId());
            $arr = array();
            foreach ($result as $key => $value) {
                $arr[] = $this->resolvePagePrivilege($value);
            }

            return $this->jsonResponse(array('type'=>'gorup','privileges'=>$arr));
        }
        return $this->jsonResponse(array());
    }
    /**
     * 保存用户、用户组的权限
     * @Route(
     *      "/privileges/", name="admin_acls_privileges_save",
     *      options = {"name":"保存权限","description":"保存用户、用户组权限列表","category":"访问控制","order":3}
     *   )
     * @Method("DELETE")
     */
    public function privilegesSaveAction(Request $request)
    {
        $to = $request->query->get('to');
        $action = $request->request->get('action');

        if( empty($to) || empty($action) ){
            $this->throwException('Params error');
        }

        $em = $this->getDoctrine()->getManager();
        //如果是给用户分配权限
        if( $action == 'user' ){
            $names = $request->request->get('names');
            $toUser = $em->getRepository('AdminUserBundle:User')->loadUserByUsername($to);
            if( !$toUser ){
                $this->throwException('User not found');
            }
            $dql = "DELETE FROM AdminAccessControlBundle:PagePrivilege dist WHERE dist.userId=:userId";
            $query = $em->createQuery($dql)->setParameter("userId", $toUser->getId());
            $query->execute();
            foreach ($names as $value) {
                $entity = new PagePrivilege();
                $entity->setRouteName($value);
                $entity->setUserId($toUser->getId());
                $entity->setGroupId(0);
                $em->persist($entity);
                $em->flush();
            }
        }
        else{
            $names = $request->request->get('names2');
            $toGroup = $em->getRepository('AdminUserBundle:Role')->findOneByName($to);
            if( !$toGroup ){
                $this->throwException('Group not found');
            }

            $dql = "DELETE FROM AdminAccessControlBundle:PagePrivilege dist WHERE dist.groupId=:groupId";
            $query = $em->createQuery($dql)->setParameter("groupId", $toGroup->getId());
            $query->execute();
            foreach ($names as $value) {
                $entity = new PagePrivilege();
                $entity->setRouteName($value);
                $entity->setGroupId($toGroup->getId());
                $entity->setUserId(0);
                $em->persist($entity);
                $em->flush();
            }
        }


        return $this->success();
    }

    private function resolvePagePrivilege($item){
        $result = array();
        $result["name"] = $item->getRouteName();
        $result["userId"] = $item->getUserId();
        $result["groupId"] = $item->getGroupId();
        return $result;
    }
}
