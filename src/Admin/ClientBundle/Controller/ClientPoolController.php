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
* @Route("/admin/clientpool")
*/
class ClientPoolController extends AdminBaseController
{
    /**
     * 所有客户列表
     * @Route(
     *      "/", name="admin_clientpool_index",
     *      options = {"name":"客户池","description":"列出系统中所有未保护客户","category":"客户管理","order":2, "show":true}
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

        $conditions = "dist.status=:status";
        $parameters = array('status'=>0);
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
        return $this->render('AdminClientBundle:ClientPool:index.html.twig', $data);
    }
    /**
     * 禁用客户 
     * @Route(
     *      "/", name="admin_clientpool_disabled",
     *      options = {"name":"客户保护","description":"保护一个客户","category":"客户管理","order":4 }
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
            $settings = $this->getSystemSettings();
            if( !$settings->enable_protection ){
                throw new \RuntimeException('protection not enabled');
            }
            $repo = $doctrine->getRepository(Client::class);
            $query = $repo->createQueryBuilder("r")
                ->where("r.id in (:ids)")
                ->setParameter(":ids", $ids)
                ->getQuery();

            $result = $query->getResult();

            foreach($result as $item){
                $this->protectClient($item, $settings->maxprotection, $settings->maxpcounts);
            }

            return $this->success();
        }

        return $this->error();   
    }

    /**
    * 执行客户保护操作
    */
    private function protectClient(Client $client,$maxprotection, $maxpcounts){
        $em = $this->getDoctrine()->getManager();
        $clients = $em->getRepository('AdminClientBundle:Client')->findByUser($this->getUser());
        if( count($clients) >= $maxpcounts ){
            throw new \RuntimeException('overflow maxpcounts');
        }
        $outtime = time()*$maxprotection*86400;
        $client->setOuttime($outtime);
        $client->setStatus(1);
        $em->flush();
    }
}
