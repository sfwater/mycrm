<?php

namespace Admin\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Admin\AdminBaseController;
use Admin\AdminAclController;
use Admin\UserBundle\Entity\Role;
use Admin\UserBundle\Form\UserType;
use Admin\UserBundle\Form\RoleSearchType;
use Symfony\Component\HttpFoundation\Request;

/**
* @Route("/admin/roles")
*/
class RoleController extends AdminAclController
{
    /**
     * 所有用户组列表 
     * @Route(
     *      "/", name="admin_roles_index",
     *      options = {"name":"用户组管理","description":"列出系统中所有用户组","category":"系统管理员","order":6, "show":true}
     *   )
     * @Method("GET") 
     */
    public function indexAction(Request $request)
    {
        $action = $request->query->get('action');
        if( $action == 'lookup' ){
            $this->setDialogMode();
        }
        $targetType = $action == "lookup" ? "dialog":"navTab";
        $options['attr']['targetType'] = $targetType;
        $options['attr']['class'] = 'form-inline searchForm';
        $params = array('action'=>$action);
        $pageSize = intval($request->query->get('pageSize'));
        if( $pageSize > 0 ){
            $params['pageSize'] = $pageSize;
        }
        $options['action'] = $this->generateUrl('admin_roles_index',$params);

        $form = $this->createForm(RoleSearchType::class, $request->query->all(), $options);

        $conditions = '';
        $parameters = array();
        if( $form->get('name')->getData() ){
            $conditions .= '(dist.name LIKE :name OR dist.role LIKE :name)';
            $parameters['name'] = '%'.$form->get('name')->getData().'%';
        }


        $sort = "dist.id DESC";
        $orderField = $request->query->get('orderField');
        $orderDirection = $request->query->get('orderDirection');
        if( !empty($orderField) && !empty($orderDirection) ){
            $sort = "dist.$orderField $orderDirection";
        }
        $data = array_merge(
            array('searchForm'=>$form->createView()),
            $this->getPagedEntities(Role::class, $conditions, $parameters, $sort)
            );
        if( empty($action) ){
            return $this->render('AdminUserBundle:Role:index.html.twig', $data);
        }
        elseif( $action == 'lookup' ){
            $mult = $request->query->get('mult');
            $data['mult'] = ($mult == 'mult');
            return $this->render('AdminUserBundle:Role:lookup.html.twig', $data);
        }
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
        $form = $this->createForm('Admin\UserBundle\Form\RoleType',$entity, array(
            'attr'=>array('class'=>'pageForm required-validate'),
            'action'=>$this->generateUrl('admin_roles_create')
        ));
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            //表单没有验证通过
            if( !$form->isValid() ){
                return $this->error($form->getErrors()->__toString());
            }
            $this->denyAccessUnlessGranted('ADD', NULL);
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            try{
                $masks = $form->get('mask')->getData();
                $mask = 0;
                foreach ($masks as $value) {
                    $mask |= $value;
                }
                $entity->setMask($mask);
                $em->persist($entity);
                $em->flush();
                $this->createAcl($entity);
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

        $entity = $em->getRepository('AdminUserBundle:Role')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Role entity.');
        }
        $this->denyAccessUnlessGranted('VIEW', $entity);


        $editForm= $this->createForm('Admin\UserBundle\Form\RoleType',$entity,array(
            'attr'=>array('class'=>'pageForm required-validate','onsuccess'=>'dialogCallback'),
            'action'=>$this->generateUrl('admin_roles_edit',array('id'=>$entity->getId()))
            ));
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $this->denyAccessUnlessGranted('EDIT', $entity);

            $masks = $editForm->get('mask')->getData();
            $mask = 0;
            foreach ($masks as $value) {
                $mask |= $value;
            }
            $entity->setMask($mask);

            $em->persist($entity);
            $em->flush();
            return $this->success();
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
        $ids = $request->request->get('ids');

        if( count($ids) > 0 ){
            $doctrine = $this->getDoctrine();
            $em = $doctrine->getManager();
            $repo = $doctrine->getRepository("AdminUserBundle:Role");
            $query = $repo->createQueryBuilder("r")
                ->where("r.id in (:ids)")
                ->setParameter(":ids", $ids)
                ->getQuery();

            $result = $query->getResult();

            foreach($result as $item){
                $this->denyAccessUnlessGranted('DELETE', $item);
                $em->remove($item);
                $em->flush();
            }

            return $this->success("delete_success");
        }

        return $this->error("delete_failure");   
    }
}
