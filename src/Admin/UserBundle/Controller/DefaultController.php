<?php

namespace Admin\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Admin\AdminBaseController;
use Admin\UserBundle\Entity\User;
use Admin\UserBundle\Form\UserType;
use Symfony\Component\HttpFoundation\Request;

/**
* @Route("/users")
*/
class DefaultController extends AdminBaseController
{
    /**
     * 所有用户列表 
     * @Route(
     *      "/", name="admin_users_index",
     *      options = {"name":"用户管理","description":"列出系统中所有管理员用户","category":"系统管理员","order":2, "show":true}
     *   )
     * @Method("GET") 
     * @Template("AdminUserBundle:Default:index.html.twig")
     */
    public function indexAction()
    {
    	$user = $this->getUser();


        return $this->datas;
    }

    /**
     * 创建一个用户 
     * @Route(
     *      "/", name="admin_users_create",
     *      options = {"name":"创建用户","description":"创建一个管理员用户账号","category":"系统管理员","order":1, "show":true}
     *   )
     * @Method("POST")
     * @Template("AdminUserBundle:Default:create.html.twig")
     */
    public function createAction(Request $request){
        $entity = new User();
        $form = $this->createForm('Admin\UserBundle\Form\UserType',$entity,array(
            'attr'=>array('class'=>'pageForm required-validate'),
            'action'=>$this->generateUrl('admin_users_create')
            ));
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            //表单没有验证通过
            if( !$form->isValid() ){
                return $this->error($form->getErrors()->__toString());
            }
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();

            try{
                //過期時間
                if( $expireTime = $form->get('expireTime')->getData() ){
                    $entity->setExpireTime(strtotime($expireTime));
                }
                if( $roleId = $form->get('roles')->getData() ){
                    $role = $em->getRepository('AdminUserBundle:Role')->find($roleId);
                    $entity->addRole($role);
                }
                $entity->setIsLocked(FALSE);
                $entity->setRegisterTime(time());
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
     * 编辑一个用户 
     * @Route(
     *      "/{id}", name="admin_users_edit",
     *      options = {"name":"编辑用户","description":"编辑一个管理员用户账号","category":"系统管理员","order":3 }
     *   )
     * @Method("POST")
     * @Template("AdminUserBundle:Default:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('AdminConsoleBundle:User')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }


        $editForm = $this->createEditForm($entity);
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
     * 禁用用户 
     * @Route(
     *      "/", name="admin_users_disabled",
     *      options = {"name":"编辑用户","description":"编辑一个管理员用户账号","category":"系统管理员","order":4 }
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
            $repo = $doctrine->getRepository("AdminUserBundle:User");
            $query = $repo->createQueryBuilder("r")
                ->where("r.id in (:ids)")
                ->setParameter(":ids", $ids)
                ->getQuery();

            $result = $query->getResult();

            foreach($result as $item){
                $em->setIsActive(FALSE);
                $em->flush();
            }

            return $this->success("delete_success");
        }

        return $this->error("delete_failure");   
    }
}
