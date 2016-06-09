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
	/**
	* 获取用户所有的路由，在options选项中设置了category的为用户路由
	*/
	protected function getUserRoutes(){
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
	* 获取不带访问控制条件的数据列表
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

        $route = $router->matchRequest($request);
        $action = $router->generate($route['_route']);


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

    protected function setNavtabMode(){
        $this->targetType = 'navTab';
    }
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
