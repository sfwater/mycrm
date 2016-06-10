<?php

namespace Admin;

use Doctrine\ORM\Mapping as ORM;


class AdminModel {
    /**
     * @ORM\ManyToOne(targetEntity="Admin\UserBundle\Entity\User")
     *
     */
    private $user;	
}

?>