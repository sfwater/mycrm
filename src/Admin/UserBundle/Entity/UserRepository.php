<?php
namespace Admin\UserBundle\Entity;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\AccountExpiredException;
use Symfony\Component\Security\Core\Exception\AccountStatusException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Doctrine\ORM\EntityRepository;

class UserRepository extends EntityRepository implements UserLoaderInterface
{
    public function loadUserByUsername($username)
    {
        $user = $this->createQueryBuilder('u')
            ->select('u, r')
            ->leftJoin('u.roles', 'r')
            ->where('u.username = :username OR u.email = :email')
            ->setParameter('username', $username)
            ->setParameter('email', $username)
            ->getQuery()
            ->getOneOrNullResult();

        if(!$user){
            $message = sprintf(
                'Unable to find an active admin user object identified by "%s".',
                $username
            );
            throw new UsernameNotFoundException($message);
        }
        //如果设置了过期时间，用户账号过期
        // if( $user->getExpireTime() && $user->getExpireTime() < time() ){
        //     $ex = new AccountExpiredException();
        //     $ex->setUser($user);
        //     throw $ex;
        // }
        //用户状态禁用
        // if( !$user->getIsActive() ){
        //     $ex = new AccountStatusException();
        //     $ex->setUser($user);
        //     throw $ex;
        // }

        return $user;
    }

    public function refreshUser(UserInterface $user)
    {
        $class = get_class($user);
        if (!$this->supportsClass($class)) {
            throw new UnsupportedUserException(
                sprintf(
                    'Instances of "%s" are not supported.',
                    $class
                )
            );
        }

        return $this->find($user->getId());
    }

    public function supportsClass($class)
    {
        return $this->getEntityName() === $class
            || is_subclass_of($class, $this->getEntityName());
    }
}
?>