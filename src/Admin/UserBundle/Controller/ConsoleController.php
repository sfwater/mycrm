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
use Admin\UserBundle\Form\RoleSearchType;
use Symfony\Component\HttpFoundation\Request;

/**
* @Route("/admin")
*/
class ConsoleController extends AdminBaseController
{
    /**
     * 修改密码
     * @Route(
     *      "/password", name="admin_change_password",
     *      options = {"name":"修改密码","description":"修改当前用户密码","category":"console","order":8, "type":"console","show":true} 
     *   )
     * @Method("PUT")
     * @Template("AdminUserBundle:Role:edit.html.twig")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('AdminConsoleBundle:User')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Role entity.');
        }


        $editForm= $this->createForm('Admin\UserBundle\Form\UserType',$entity);
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
}
