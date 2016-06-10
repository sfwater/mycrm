<?php
namespace Admin\AccessControlBundle\Security;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Admin\UserBundle\Entity\User;
use Admin\AccessControlBundle\Entity\DataPrivilege;
use Admin\AccessControlBundle\Entity\PagePrivilege;
use Admin\AccessControlBundle\Entity\UserTree;
use Doctrine\Common\Util\ClassUtils;

class DataAccessControlVoter extends Voter
{

    private $router;
    private $ignores = array('admin_index');
    private $doctrine;
    private $adminUser = 'admin';

    const VIEW = 'VIEW';
    const ADD = 'ADD';
    const EDIT = 'EDIT';
    const DELETE = 'DELETE';

    public function __construct($router,$doctrine){
        $this->router = $router;
        $this->doctrine = $doctrine;
    }

    protected function supports($attribute, $subject)
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, array(self::VIEW, self::EDIT, self::ADD, self::DELETE))) {
            return false;
        }
        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        if( $this->isSuperAdmin($user) ){
            return true;
        }

        $em = $this->doctrine->getManager();
        //如果是查看的权限，则判断用户关系树中是否有权限
        if( $attribute == self::VIEW ){
            //找到数据所属的用户ID
            $acl = $em->getRepository(DataPrivilege::class)->findOneBy(array('className'=>ClassUtils::getClass($subject),'identifier'=>$subject->getId()));
            //如果没有权限则拒绝访问
            if(!$acl){
                return false;
            }

            //如果是自己的数据则授权
            if( $user->getId() == $acl->getUserId() ){
                return true;
            }
            //如果有子用户权限，则授权
            $childUsers = $em->getRepository(UserTree::class)->findByParentId($user->getId());

            if( $childUsers ){
                foreach ($childUsers as $key => $value) {
                    if( $value->getUserId() == $acl->getUserId() ){
                        return true;
                    }
                }
            }
        }
        elseif( $attribute == self::ADD ){
            //添加数据则验证所在用户组的权限
            $role = $user->getRoles()[0];
            if( $role ){
                $mask = $role->getMask();
                return (1 & $mask) > 0;
            }
        }
        elseif( $attribute == self::EDIT ){
            $role = $user->getRoles()[0];
            if( $role ){
                $mask = $role->getMask();
                return (2 & $mask) > 0;
            }
        }
        elseif ($attribute == self::DELETE) {
            $role = $user->getRoles()[0];
            if( $role ){
                $mask = $role->getMask();
                return (4 & $mask) > 0;
            }
        }

        return false;
    }

    /**
    * 获取用户权限、包含用户组的
    */

    protected function getUserRoutes($user){
        $em = $this->doctrine->getManager();
        $dql = "SELECT dist FROM AdminAccessControlBundle:PagePrivilege dist WHERE dist.userId=:userId OR dist.groupId=:groupId";
        $userId = $user->getId();
        $groupId = 0;
        if( count($user->getRoles()) > 0 ){
            $role = $user->getRoles()[0];
            $groupId = $role->getId();
        }
        $query = $em->createQuery($dql)->setParameters(array('userId'=>$userId, 'groupId'=>$groupId));
        return $query->getResult();
    }
    /**
    * 判断一个用户是否为超级管理员
    */
    protected function isSuperAdmin($user){
        return $user->getUsername() == $this->adminUser;
    }
}
?>