<?php

namespace Admin\ConsoleBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Admin\AdminBaseController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Admin\ConsoleBundle\Form\SiteConfigType;
use Symfony\Component\HttpFoundation\Request;
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
        $configs = $this->getSystemConfigs();
        $routes = $this->resolveUserRoutes($this->getUserRoutes());
        $settings = $this->getSystemSettings();
        return $this->render('AdminConsoleBundle:Default:index.html.twig', array(
            'userRoutes'=>$routes,
            'configs'=>$configs,
            'settings'=>$settings
            ));
    }

    /**
     * 系统配置
     * @Route(
     *      "/config", name="admin_configuration",
     *      options = {"name":"系统配置","description":"修改系统配置","category":"console","order":7, "type":"console","show":true,"target":"dialog"} 
     *   )
     * @Method("POST")
     * @Template("AdminConsoleBundle:Default:config.html.twig")
     */
    public function configAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('AdminConsoleBundle:SiteConfig')->findOne();

        if (!$entity) {
            $entity = new SiteConfig();
        }


        $editForm= $this->createForm(SiteConfigType::class,$entity);
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
