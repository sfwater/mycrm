<?php

namespace Admin\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
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
     *      "/", name="admin_users_index",
     *      options = {"name":"用户管理","description":"列出系统中所有管理员用户","category":"AdminUser","order":2}
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
     *      options = {"name":"创建用户","description":"创建一个管理员用户账号","category":"AdminUser","order":1}
     *   )
     * @Method("POST")
     * @Template("AdminUserBundle:Default:create.html.twig")
     */
    public function createAction(){
        $entity = new User();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();

            $entity->setRegisterTime(time());
            try{
                $entity->setUser($user);
                $entity->setConsumption(0);
                $entity->setSubcount(0);
                $entity->setBalance(0);
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

    private function createCreateForm(User $entity)
    {
        $form = $this->createForm(new UserType(), $entity, array(
            'action' => $this->generateUrl('admin_users_create'),
            'method' => 'POST',
            "attr" => array("class"=>"pageForm required-validate","onsubmit"=>"return validateCallback(this,navTabAjaxDone);")
        ));

        return $form;
    }
}
