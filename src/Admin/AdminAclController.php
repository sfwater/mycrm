<?php

namespace Admin;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Admin\AccessControlBundle\Entity\DataPrivilege;
use Admin\AccessControlBundle\Entity\PagePrivilege;
use Doctrine\Common\Util\ClassUtils;
use Admin\UserBundle\Entity\User;

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
	* 获取当前用户可访问的数据列表
	*/
	protected function getEntities($class, $conditions='', $parameters=array(), $sort='dist.id DESC', $join=''){
		$user = $this->getUser();
		if( !$this->isSuperAdmin($user) ){
			$aclCondition = 'dist.id IN (SELECT acls.identifier FROM AdminAccessControlBundle:DataPrivilege acls WHERE acls.userId IN (SELECT tree.userId FROM AdminAccessControlBundle:UserTree tree WHERE tree.parentId=:userId) OR acls.userId=:userId)';
			if( empty($conditions) ){
				$conditions = $aclCondition;
			}
			else{
				$conditions .= ' AND '.$aclCondition;
			}

			$parameters['userId'] = $user->getId();
		}
		return parent::getEntities($class, $conditions, $parameters, $sort, $join);
	}
	/**
	* 获取当前用户可访问的分页数据列表
	*/
	protected function getPagedEntities($class, $conditions='', $parameters = array(), $sort='dist.id DESC', $join=''){
		$user = $this->getUser();
		if( !$this->isSuperAdmin($user) ){
			$aclCondition = 'dist.id IN (SELECT acls.identifier FROM AdminAccessControlBundle:DataPrivilege acls WHERE acls.userId IN (SELECT tree.userId FROM AdminAccessControlBundle:UserTree tree WHERE tree.parentId=:userId) OR acls.userId=:userId)';
			if( empty($conditions) ){
				$conditions = $aclCondition;
			}
			else{
				$conditions .= ' AND '.$aclCondition;
			}

			$parameters['userId'] = $user->getId();
		}
		return parent::getPagedEntities($class, $conditions, $parameters, $sort, $join);
	}

	/**
	* 创建数据权限
	*/
	protected function creatAcl($entity, $user = NULL){
		if( $user == NULL ){
			$user = $this->getUser();
		}
		$em = $this->getDoctrine()->getManager();

		//确保没有创建
		$class = ClassUtils::getClass($entity);
		$one = $em->getRepository(DataPrivilege::class)->findBy(array('className'=>$class, 'identifier'=>$entity->getId()));
		if( !$one ){
			$privilege = new DataPrivilege();
			$privilege->setClassName($class);
			$privilege->setIdentifier($entity->getId());
			$privilege->setUserId($user->getId());
			$privilege->setMask(0);
			$em->persist($privilege);
			$em->flush();
		}
	}
    /**
    * 获取用户可用用户，除去admin账号
    */
    protected function getSystemAvaliableUsers(){
        return $this->getEntities(User::class, 'dist.username<>:admin', array('admin'=>$this->adminUser));
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
