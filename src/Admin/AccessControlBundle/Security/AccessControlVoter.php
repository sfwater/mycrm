<?php
namespace Admin\AccessControlBundle\Security;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Admin\UserBundle\Entity\User;

class AccessControlVoter extends Voter
{

    private $router;
    private $ignores = array('admin_index');
    private $doctrine;
    private $adminUser = 'admin';

    public function __construct($router,$doctrine){
        $this->router = $router;
        $this->doctrine = $doctrine;
    }

    protected function supports($attribute, $subject)
    {
        //访问控制
        return get_class($subject) == 'Symfony\Component\HttpFoundation\Request';
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


        if( $route = $this->router->matchRequest($subject) ){
            $matchedRouteName = $route['_route'];
            if( in_array($matchedRouteName, $this->ignores) ){
                return true;
            }

            $privileges = $this->doctrine->getManager()->getRepository('AdminAccessControlBundle:PagePrivilege')->findByUserId($user->getId());

            foreach ($privileges as $key => $value) {
                $name = $value->getRouteName();
                //用户权限列表中有
                if( $name == $matchedRouteName ){
                    return true;
                }
            }
        }

        return false;
    }
    /**
    * 判断一个用户是否为超级管理员
    */
    protected function isSuperAdmin($user){
        return $user->getUsername() == $this->adminUser;
    }
}
?>