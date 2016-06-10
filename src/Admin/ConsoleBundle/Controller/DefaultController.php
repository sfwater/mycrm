<?php

namespace Admin\ConsoleBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Admin\AdminBaseController;
use Admin\AdminAclController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Admin\ConsoleBundle\Form\SiteConfigType;
use Admin\ConsoleBundle\Entity\SiteConfig;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Util\ClassUtils;
/**
* @Route("/admin")
*/
class DefaultController extends AdminAclController 
{
    var $uploadDir = 'file/attachment';
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

        $entity = $em->getRepository('AdminConsoleBundle:SiteConfig')->findAll();

        if (!$entity) {
            $entity = new SiteConfig();
        }
        else{
            $entity = $entity[0];
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
            'settings'=> json_decode($entity->getConfig())
        );
    }
    /**
     * 文件上传 
     * @Route(
     *      "/upload", name="admin_file_upload",
     *      options = {"name":"文件上传","description":"文件上传接口","category":"console","order":8, "type":"console"} 
     *   )
     * @Method("POST")
     */
    public function uploadAction(Request $request){
       //处理上传文件 
        $file = $request->files->get('attachment');

        if( !is_dir($this->uploadDir) ){
            mkdir($this->uploadDir, 0777, true);
        }

        $ext = $file->guessExtension();
        if( !$ext ){
            $ext =  "bin";
        }
        $filename = $this->randomName().".".$ext;
        $file->move($this->uploadDir, $filename);

        $ret = new \StdClass();
        $ret->realPath = $this->uploadDir."/".$filename;
        $ret->webPath = "/".$this->uploadDir."/".$filename;

        return $this->jsonResponse($ret);
    }

    /**
    * @Route("/test",name="console_test")
    */
    public function testAction(){
        $user = $this->getUser();
        dump(ClassUtils::getRealClass("AdminUserBundle:User"));
        exit;
    }
    //生成随机文件名
    private function randomName(){
        srand((double)microtime()*1000000); 
        return date("YmdHis").rand(0,1000);
    }
}
