<?php

namespace Admin;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
* 管理员控制器基类
*/
class AdminBaseController extends Controller
{
	/**
	* 获取用户所有的路由，在options选项中设置了category的为用户路由
	*/
	protected function getUserRoutes(){
    	$router = $this->get("router");
    	$allRoutes = $router->getRouteCollection();
    	$userRoutes = $allRoutes->all();
    	$routes = array();

    	foreach ($userRoutes as $key => $value) {
    		$options = $value->options;
    		if( array_key_exists('category', $options) ){
    			$routes[$key] = $value;
    		}
    	}
    	return $routes;
	}
}
