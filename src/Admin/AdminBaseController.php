<?php

namespace Admin;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
* 管理员控制器基类
*/
class AdminBaseController extends Controller
{
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
    		if( array_key_exists('category', $options) ){
    			$routes[] = $value;
    		}
    		$value->order = isset($options['order']) ? intval($options['order']) : 0;
    		$value->name = $key;
    		$value->show = isset($options['show']) ? $options['show'] : false;
    	}
    	return $routes;
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
	* 执行成功方法
	*/
	protected function success($info='数据保存成功'){
		$data = new \StdClass();
		$data->statusCode = 200;
		$data->message = $info;
		return new JsonResponse($data);
	}

	/**
	* 执行失败方法
	*/
	protected function error($info='数据保存失败'){
		$data = new \StdClass();
		$data->statusCode = 500;
		$data->message = $info;
		return new JsonResponse($data);
	}
}
