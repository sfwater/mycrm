<?php

namespace Admin\ClientBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Admin\AdminBaseController;
use Admin\AdminAclController;
use Admin\ClientBundle\Entity\Client;
use Admin\UserBundle\Entity\User;
use Admin\ClientBundle\Form\ClientType;
use Admin\ClientBundle\Form\ClientSearchType;
use Symfony\Component\HttpFoundation\Request;
/**
* @Route("/admin/clients")
*/
class DefaultController extends AdminAclController
{
    /**
     * 所有客户列表
     * @Route(
     *      "/", name="admin_clients_index",
     *      options = {"name":"我的客户","description":"列出系统中所有客户","category":"客户管理","order":2, "show":true}
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
        $options['action'] = $this->generateUrl('admin_clients_index',$params);
        $form = $this->createForm(ClientSearchType::class, $request->query->all(), $options);

        $conditions = "";
        $parameters = array();
        if( $form->get('name')->getData() ){
            $conditions .= '(dist.name LIKE :name OR dist.contactor LIKE :name OR dist.contact LIKE :name)';
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
            $this->getPagedEntities(Client::class, $conditions, $parameters, $sort)
            );
        return $this->render('AdminClientBundle:Default:index.html.twig', $data);
    }
    /**
     * 创建一个客户
     * @Route(
     *      "/", name="admin_clients_create",
     *      options = {"name":"创建客户","description":"创建一个客户","category":"客户管理","order":1, "show":true}
     *   )
     * @Method("POST")
     * @Template("AdminClientBundle:Default:create.html.twig")
     */
    public function createAction(Request $request){
        $entity = new Client();
        $form = $this->createForm(ClientType::class,$entity,array(
            'attr'=>array('class'=>'pageForm required-validate'),
            'action'=>$this->generateUrl('admin_clients_create')
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
                if( $wtime = $form->get('wtime')->getData() ){
                    $entity->setWtime(strtotime($wtime));
                }
                $entity->setCtime(time());
                $entity->setStatus(0);


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
     * 编辑一个客户 
     * @Route(
     *      "/{id}", name="admin_clients_edit",
     *      options = {"name":"编辑客户","description":"编辑一个客户","category":"客户管理","order":3 }
     *   )
     * @Method("POST")
     * @Template("AdminClientBundle:Default:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository(Client::class)->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Client entity.');
        }
        $this->denyAccessUnlessGranted('VIEW', $entity);


        $editForm = $this->createForm(ClientType::class,$entity,array(
            'attr'=>array('class'=>'pageForm required-validate','onsuccess'=>'dialogCallback'),
            'action'=>$this->generateUrl('admin_clients_edit',array('id'=>$entity->getId()))
            ));
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $this->denyAccessUnlessGranted('EDIT', $entity);
            if( $wtime = $form->get('wtime')->getData() ){
                $entity->setWtime(strtotime($wtime));
            }
            
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
     * 禁用客户 
     * @Route(
     *      "/", name="admin_clients_disabled",
     *      options = {"name":"客户状态","description":"删除、启用、禁用一个客户","category":"客户管理","order":4 }
     *   )
     * @Method("DELETE")
     */
    public function deleteAction(Request $request)
    {
        $id = intval($request->query->get("id"));

        $ids = $request->request->get("ids");

        $action = $request->request->get('action');

        if( empty($action) ){
            return $this->error('missing action');
        }

        if( $id > 0 ){
            $ids[] = $id;
        }
        if( count($ids)>0 ){
            $doctrine = $this->getDoctrine();
            $em = $doctrine->getManager();
            $repo = $doctrine->getRepository(Client::class);
            $query = $repo->createQueryBuilder("r")
                ->where("r.id in (:ids)")
                ->setParameter(":ids", $ids)
                ->getQuery();

            $result = $query->getResult();

            foreach($result as $item){
                $this->denyAccessUnlessGranted('DELETE', $item);
                if( $action == 'delete' ){
                    $em->remove($item);
                }
                $em->flush();
            }

            return $this->success();
        }

        return $this->error();   
    }
}
