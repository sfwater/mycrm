<?php

namespace Admin\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Admin\AdminBaseController;
use Admin\UserBundle\Entity\Role;
use Admin\UserBundle\Form\UserType;
use Symfony\Component\HttpFoundation\Request;

/**
* @Route("/roles")
*/
class RoleController extends AdminBaseController
{
    /**
     * 所有用户组列表 
     * @Route(
     *      "/", name="admin_roles_index",
     *      options = {"name":"用户组管理","description":"列出系统中所有用户组","category":"系统管理员","order":6, "show":true}
     *   )
     * @Method("GET") 
     * @Template("AdminUserBundle:Role:index.html.twig")
     */
    public function indexAction()
    {
    	$user = $this->getUser();


        return $this->datas;
    }

    /**
     * 创建一个用户组 
     * @Route(
     *      "/", name="admin_roles_create",
     *      options = {"name":"创建用户组","description":"创建一个用户组","category":"系统管理员","order":5, "show":true}
     *   )
     * @Method("POST")
     * @Template("AdminUserBundle:Role:create.html.twig")
     */
    public function createAction(Request $request){
        $entity = new Role();
        $form = $this->createForm('Admin\UserBundle\Form\RoleType',$entity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();

            $entity->setRegisterTime(time());
            try{
                $em->persist($entity);
                $em->flush();
                $em->getConnection()->commit();
                return $this->success();
            }catch(Exception $e){
                $em->getConnection()->rollback();
                $em->close();
                throw $e;
            }
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }
    /**
     * 编辑一个用户组 
     * @Route(
     *      "/{id}", name="admin_roles_edit",
     *      options = {"name":"编辑用户组","description":"编辑一个用户组","category":"系统管理员","order":7 }
     *   )
     * @Method("POST")
     * @Template("AdminUserBundle:Role:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('AdminConsoleBundle:Role')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Role entity.');
        }


        $editForm= $this->createForm('Admin\UserBundle\Form\RoleType',$entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();
            return $this->success();
        }
        else{
            $editForm["state"]->setData($entity->getUser()->getIsActive());
        }

        return array(
            'entity'      => $entity,
            'form'   => $editForm->createView(),
        );
    }
    /**
     * 删除用户组
     * @Route(
     *      "/", name="admin_roles_delete",
     *      options = {"name":"删除用户组","description":"删除一个用户组","category":"系统管理员","order":8 }
     *   )
     * @Method("DELETE")
     */
    public function deleteAction(Request $request)
    {
        $id = intval($request->query->get("id"));

        $ids = $request->request->get("ids");

        if( $id > 0 ){
            $ids[] = $id;
        }
        if( count($ids)>0 ){
            $doctrine = $this->getDoctrine();
            $em = $doctrine->getManager();
            $repo = $doctrine->getRepository("AdminUserBundle:Role");
            $query = $repo->createQueryBuilder("r")
                ->where("r.id in (:ids)")
                ->setParameter(":ids", $ids)
                ->getQuery();

            $result = $query->getResult();

            foreach($result as $item){
                $em->remove($item);
                $em->flush();
            }

            return $this->success("delete_success");
        }

        return $this->error("delete_failure");   
    }
}
