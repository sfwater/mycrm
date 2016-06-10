<?php
namespace Admin\AccessControlBundle\Security;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Admin\UserBundle\Entity\User;

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
        dump($attribute);
        return false;
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