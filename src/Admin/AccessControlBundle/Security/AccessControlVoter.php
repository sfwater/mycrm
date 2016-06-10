<?php
namespace Admin\AccessControlBundle\Security;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Admin\UserBundle\Entity\User;

class AccessControlVoter extends Voter
{
    // these strings are just invented: you can use anything
    const VIEW = 'view';
    const EDIT = 'edit';

    private $router;
    private $ignores = array('admin_index');
    private $doctrine;

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

        if( $route = $this->router->matchRequest($subject) ){
            if( in_array($route->name, $this->ignores) ){
                return true;
            }

            $privileges = $this->doctrine->getManager()->getRepository('AdminAccessControlBundle:PagePrivilege')->findByUserId($user->getId());

            foreach ($privileges as $key => $value) {
                $name = $value->getRouteName();
                //用户权限列表中有
                if( $name == $route->name ){
                    return true;
                }
            }
        }

        return false;
    }
}
?>