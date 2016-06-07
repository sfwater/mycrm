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
        $user = $this->getUser();

        if (!$user) {
            throw $this->createNotFoundException('Unable to find user entity.');
        }


        $form= $this->createFormBuilder()

            ->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em->flush();
            return $this->success();
        }

        return array(
            'form'   => $editForm->createView(),
        );
    }
}
