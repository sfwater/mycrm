<?php

namespace Admin;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
* 管理员ACL控制器基类
*/
class AdminAclController extends AdminBaseController
{
	protected function getUserRoutes($user = NULL){
		if( $user == NULL ){
			$user = $this->getUser();
		}

		$routes = parent::getUserRoutes($user);
		if( $this->isSuperAdmin($user) ){
			return $routes;
		}
		else{
			$_routes = array();
			$em = $this->getDoctrine()->getManager();
			$result = $em->getRepository('AdminAccessControlBundle:PagePrivilege')->findByUserId($user->getId());
			foreach ($result as $key => $value) {
				$name = $value->getRouteName();
				if( array_key_exists($name, $routes) ){
					$_routes[$name] = $routes[$name];
				}
			}
			return $_routes;
		}
	}
}
