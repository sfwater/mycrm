<?php

namespace Admin;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
* 管理员控制器基类
*/
class AdminBaseController extends Controller
{
    protected $targetType = 'navTab';
    protected $adminUser = 'admin';

	/**
	* 获取用户所有的路由，在options选项中设置了category的为用户路由
	*/
	protected function getUserRoutes($user = NULL){
    	$router = $this->get('router');
    	$allRoutes = $router->getRouteCollection();
    	$userRoutes = $allRoutes->all();
    	$routes = array();

    	foreach ($userRoutes as $key => $value) {
    		$options = $value->getOptions();
    		$value->order = isset($options['order']) ? intval($options['order']) : 0;
    		$value->name = $key;
    		$value->show = isset($options['show']) ? $options['show'] : false;
    		$value->type = isset($options['type']) ? $options['type'] : 'menu';
    		$value->target = isset($options['target']) ? $options['target'] : 'navTab';
    		if( array_key_exists('category', $options) ){
    			$routes[] = $value;
    		}
    	}
    	return $routes;
	}

    /**
    * 获取不带访问控制条件的数据
    */
    protected function getEntities($class, $conditions='', $parameters=array(), $sort='dist.id DESC', $join=''){
        $em = $this->getDoctrine()->getManager();
        $dql = "SELECT dist FROM $class dist $join WHERE $conditions ORDER BY $sort";
        $query = $em->createQuery($dql)->setParameters($parameters);        
        $results = $query->getResult();
        return $results;
    }
	/**
	* 获取不带访问控制条件的分页数据列表
	*/
	protected function getPagedEntities($class, $conditions='', $parameters = array(), $sort='dist.id DESC', $join=''){
        $request = $this->get('request_stack')->getCurrentRequest();
        $pager = $this->get('admin_console.pager');
        $router = $this->get('router');
        $em = $this->getDoctrine()->getManager();

        $page = intval($request->query->get('page'));
        if( $page < 1 ){
        	$page = 1;
        }
        //分页大小
        $pageSize = intval($request->query->get('pageSize'));
        if( $pageSize > 0 ){
            $pager->setPageSize($pageSize);
        }
        $pageSize = $pager->getPageSize();

        if( $conditions == '' ){
        	$conditions = '1=1';
        }

        $dql = "SELECT dist FROM $class dist $join WHERE $conditions ORDER BY $sort";

        $query = $em->createQuery($dql);
        $query->setParameters($parameters);
        $query->setFirstResult(($page-1)*$pageSize);
        $query->setMaxResults($pageSize);
        $paginator = new Paginator($query, TRUE);
        $counts = count($paginator);
        $results = $query->getResult();


        $pager->setTotalRows($counts);
        $pager->setCurrentPage($page);

        //取得所有当前的参数列表
        $qs = $request->query->all();

        if( array_key_exists('page', $qs) ){
        	unset($qs['page']);
        }
        $baseUrl = '?';
        if( count($qs) > 0 ){
        	foreach ($qs as $key => $value) {
        		if( is_array($value) ){
        			foreach ($value as $k => $v) {
        				$baseUrl .= "$k=$v&";
        			}
        		}
        		else{
	        		$baseUrl .= "$key=$value&";
        		}
        	}
        }
        $pager->setBaseUrl($baseUrl);
        $pager->setTargetType($this->targetType);

        // $route = $router->matchRequest($request);
        // $action = $router->generate($route['_route']);


        return array(
            'counts'=>$counts, 
            'results'=>$results, 
            'pageSize'=>$pageSize, 
            'pager'=>$pager->pagination('1'),
            'pageLength'=>$pager->pageAmount(),
            'query'=>$request->query->all(),
            'targetType'=>$this->targetType
            );
	}


    /**
    * 判断一个用户是否为超级管理员
    */
    protected function isSuperAdmin($user = NULL){
        if( $user == NULL ){
            $user = $this->getUser();
        }
        return $user->getUsername() == $this->adminUser;
    }

    /**
    * 获取系统可用用户，除去admin账号
    */
    protected function getSystemAvaliableUsers(){
        $em = $this->getDoctrine()->getManager();
        $dql = "SELECT dist FROM AdminUserBundle:User dist WHERE dist.username<>'{$this->adminUser}'";
        return $em->createQuery($dql)->getResult();
    }

    /**
    * 设置表单模式为控制台
    */
    protected function setNavtabMode(){
        $this->targetType = 'navTab';
    }
    /**
    * 设置表单模式为对话框
    */
    protected function setDialogMode(){
        $this->targetType = 'dialog';
    }

	/**
	* 分组、排序用户路由
	*/
	protected function resolveUserRoutes($routes){
		//排序数组
		$groups = array();
		for ($i=0; $i < count($routes); $i++) { 
			for ($j=$i+1; $j < count($routes); $j++) { 
				if( $routes[$i]->order > $routes[$j]->order ){
					$tmp = $routes[$i];
					$routes[$i] = $routes[$j];
					$routes[$j] = $tmp;
				}
			}
		}

		foreach ($routes as $value) {
			$options = $value->getOptions();
			if( !array_key_exists($options['category'], $groups) ){
				$groups[$options['category']] = array();
			}
		}

		foreach ($groups as $group_key => $group) {
			foreach ($routes as $key => $value) {
				$options = $value->getOptions();
				if( $options['category'] == $group_key ){
					$groups[$group_key][] = $value;
				}
			}
		}
		return $groups;
	}

    /**
    * 获取系统设置
    */
    protected function getSystemSettings(){
        $em = $this->getDoctrine()->getManager();
        $settings = $em->getRepository('AdminConsoleBundle:SiteConfig')->findAll();
        if( $settings ){
            $settings = json_decode($settings[0]->getConfig());
        }
        return $settings;
    }

    /**
    * 获取系统配置
    */
    protected function getSystemConfigs(){
        $configs = $this->container->getParameter('admin_console');
        return $configs;
    }

    /**
    * 抛出异常
    */
    protected function throwException($msg){
        throw new \Exception($this->translate($msg));
    }

    /**
    * 翻译
    */
    protected function translate($msg){
        return $this->get('translator')->trans($msg);
    }


    /**
    * 返回JSON数据
    */
    protected function jsonResponse($json){
        $data = new \StdClass();
        $data->statusCode = 200;
        $data->message = '';
        $data->data = $json;
        return new JsonResponse($data);
    }
	/**
	* 执行成功方法
	*/
	protected function success($info='submit_success'){
		$data = new \StdClass();
		$data->statusCode = 200;
		$data->message = $this->translate($info);
		return new JsonResponse($data);
	}

	/**
	* 执行失败方法
	*/
	protected function error($info='submit_failure'){
		$data = new \StdClass();
		$data->statusCode = 500;
		$data->message = $this->translate($info);
		return new JsonResponse($data, 500);
	}
}
