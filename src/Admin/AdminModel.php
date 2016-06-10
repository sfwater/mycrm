<?php

namespace Admin;

use Doctrine\ORM\Mapping as ORM;

/**
* 此类无用
*/
class AdminModel {
    /**
     * @ORM\ManyToOne(targetEntity="Admin\UserBundle\Entity\User")
     *
     */
    private $user;	
}

?>