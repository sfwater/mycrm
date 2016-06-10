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
    public function __construct($router){
        $this->router = $router;
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
        }

        return false;
    }
}
?>