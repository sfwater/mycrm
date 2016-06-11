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
use Admin\ClientBundle\Entity\ClientAccessRecord;
use Admin\UserBundle\Entity\User;
use Admin\ClientBundle\Form\ClientAccessRecordType;
use Admin\ClientBundle\Form\ClientAccessSearchType;
use Symfony\Component\HttpFoundation\Request;
/**
* @Route("/admin/clients")
*/
class ClientAccessRecordController extends AdminBaseController
{
    /**
     * 客户回访记录
     * @Route(
     *      "/{id}/records", name="admin_client_access_records_index",
     *      options = {"name":"回访跟踪","description":"列出客户的回访记录","category":"客户管理","order":2}
     *   )
     * @Method("GET") 
     */
    public function indexAction(Request $request, $id)
    {
        $action = $request->query->get('action');
        if( $action == 'lookup' ){
            $this->setDialogMode();
        }
        $targetType = $action == "lookup" ? "dialog":"navTab";
        $options['attr']['targetType'] = $targetType;
        $options['attr']['class'] = 'form-inline searchForm';
        $params = array('action'=>$action,'id'=>$id);
        $pageSize = intval($request->query->get('pageSize'));
        if( $pageSize > 0 ){
            $params['pageSize'] = $pageSize;
        }
        $options['action'] = $this->generateUrl('admin_client_access_records_index',$params);
        $form = $this->createForm(ClientAccessSearchType::class, $request->query->all(), $options);

        $conditions = "dist.client=:client";
        $parameters = array('client'=>$id);
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
            array('searchForm'=>$form->createView(),'clientId'=>$id),
            $this->getPagedEntities(ClientAccessRecord::class, $conditions, $parameters, $sort)
            );
        return $this->render('AdminClientBundle:ClientAccessRecord:index.html.twig', $data);
    }
    /**
     * 创建一个回访记录
     * @Route(
     *      "/{id}/records", name="admin_client_access_records_create",
     *      options = {"name":"录入回访","description":"录入一个客户的回访记录","category":"客户管理","order":1}
     *   )
     * @Method("POST")
     * @Template("AdminClientBundle:ClientAccessRecord:create.html.twig")
     */
    public function createAction(Request $request,$id){
        $entity = new ClientAccessRecord();
        $form = $this->createForm(ClientAccessRecordType::class,$entity,array(
            'attr'=>array('class'=>'pageForm required-validate','onsuccess'=>'recordsUpdated'),
            'action'=>$this->generateUrl('admin_client_access_records_create',array('id'=>$id))
            ));
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            //表单没有验证通过
            if( !$form->isValid() ){
                return $this->error($form->getErrors()->__toString());
            }
            $this->denyAccessUnlessGranted('ADD', NULL);
            $em = $this->getDoctrine()->getManager();
            $client = $em->getRepository(Client::class)->find($id);
            if( !$client ){
                $this->throwException('Client not found'); 
            }
            $em->getConnection()->beginTransaction();

            try{
                if( $wtime = $form->get('wtime')->getData() ){
                    //设置客户的下次回访时间
                    if(time()>strtotime($wtime)){
                        $this->throwException('time is too neer');
                    }
                    $client->setWtime(strtotime($wtime));
                }
                $entity->setCtime(time());
                $entity->setClient($client);

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
     * 编辑一个回访记录
     * @Route(
     *      "/{cid}/records/{id}", name="admin_client_access_records_edit",
     *      options = {"name":"编辑回访","description":"编辑一个回访记录","category":"客户管理","order":3 }
     *   )
     * @Method("POST")
     * @Template("AdminClientBundle:ClientAccessRecord:edit.html.twig")
     */
    public function updateAction(Request $request, $cid, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository(ClientAccessRecord::class)->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Client entity.');
        }
        $this->denyAccessUnlessGranted('VIEW', $entity);


        $editForm = $this->createForm(ClientAccessRecordType::class,$entity,array(
            'attr'=>array('class'=>'pageForm required-validate','onsuccess'=>'recordsUpdated'),
            'action'=>$this->generateUrl('admin_client_access_records_edit',array('cid'=>$cid,'id'=>$entity->getId()))
            ));
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $this->denyAccessUnlessGranted('EDIT', $entity);
            if( $wtime = $editForm->get('wtime')->getData() ){
                if(time()>strtotime($wtime)){
                    $this->throwException('time is too neer');
                }
                $entity->getClient()->setWtime(strtotime($wtime));
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
     * 删除回访记录
     * @Route(
     *      "/{cid}/records", name="admin_client_access_records_disabled",
     *      options = {"name":"删除回访","description":"删除一条回访记录","category":"客户管理","order":4 }
     *   )
     * @Method("DELETE")
     */
    public function deleteAction(Request $request,$cid)
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
            $repo = $doctrine->getRepository(ClientAccessRecord::class);
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
