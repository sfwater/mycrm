<?php



namespace Admin\VerifyBundle\Exception;

use Symfony\Component\Security\Core\Exception\AuthenticationException;


class BadVerifycodeException extends AuthenticationException
{
    /**
     * {@inheritdoc}
     */
    public function getMessageKey()
    {
        return 'Invalid vcode.';
    }
}
