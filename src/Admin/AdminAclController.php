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
	/**
	* 获取用户权限列表
	*/
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
			$userId = $user->getId();
			$groupId = 0;
			if( count($user->getRoles()) > 0 ){
				$role = $user->getRoles()[0];
				$groupId = $role->getId();
			}
			$dql = "SELECT dist FROM AdminAccessControlBundle:PagePrivilege dist WHERE dist.userId=:userId OR dist.groupId=:groupId";
			$query = $em->createQuery($dql)->setParameters(array('userId'=>$userId, 'groupId'=>$groupId));
			$result = $query->getResult();
			foreach ($result as $key => $value) {
				$name = $value->getRouteName();
				if( $route = $this->findRouteByName($routes,$name) ){
					$_routes[] = $route;
				}
			}
			return $_routes;
		}
	}

	/**
	* 创建数据权限
	*/
	protected function creatAcl($entity, $user = NULL){
		if( $user == NULL ){
			$user = $this->getUser();
		}


	}
	/**
	* 根据名称获取路由信息
	*/
	private function findRouteByName($routes, $name){
		foreach ($routes as $key => $value) {
			if( $name == $value->name ){
				return $value;
			}
		}
		return FALSE;
	}
}
