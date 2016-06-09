<?php

namespace Admin\ConsoleBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Admin\AdminBaseController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
/**
* @Route("/admin")
*/
class DefaultController extends AdminBaseController
{
    /**
     * @Route("/", name="admin_index")
     */
    public function indexAction()
    {
        $configs = $this->container->getParameter('admin_console');
        $routes = $this->resolveUserRoutes($this->getUserRoutes());
        return $this->render('AdminConsoleBundle:Default:index.html.twig', array(
            'userRoutes'=>$routes,
            'configs'=>$configs,
            ));
    }

    /**
     * 系统配置
     * @Route(
     *      "/config", name="admin_configuration",
     *      options = {"name":"系统配置","description":"修改系统配置","category":"console","order":7, "type":"console","show":true,target="dialog"} 
     *   )
     * @Method("POST")
     * @Template("AdminConsoleBundle:Default:config.html.twig")
     */
    public function configAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('AdminConsoleBundle:SiteConfig')->findOne();

        if (!$entity) {
            $entity = new SiteConfig();
        }


        $editForm= $this->createForm('Admin\ConsoleBundle\Form\SiteConfigType',$entity,array(
            'attr'=>array('class'=>'pageForm required-validate','onsuccess'=>'dialogCallback'),
            'action'=>$this->generateUrl('admin_configuration')
            ));
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $settings = $request->request->get('settings');
            $entity->setConfig(json_encode($settings));
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
    * @Route("/test",name="console_test")
    */
    public function testAction(){
        $router = $this->get("router");
        $request = $this->get("request_stack")->getCurrentRequest();
        $route = $router->matchRequest($request);
        dump($router->generate($route['_route']));
        exit;
    }
}
