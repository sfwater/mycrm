<?php

namespace Admin\AccessControlBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PagePrivilege
 *
 * @ORM\Table(name="page_privileges")
 * @ORM\Entity(repositoryClass="Admin\AccessControlBundle\Repository\PagePrivilegeRepository")
 */
class PagePrivilege
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="route_name", type="string", length=255)
     */
    private $routeName;

    /**
     * @var integer 
     *
     * @ORM\Column(name="user_id", type="integer")
     */
    private $userId;

    /**
     * @var integer 
     *
     * @ORM\Column(name="group_id", type="integer")
     */
    private $groupId;

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set routeName
     *
     * @param string $routeName
     *
     * @return PagePrivilege
     */
    public function setRouteName($routeName)
    {
        $this->routeName = $routeName;

        return $this;
    }

    /**
     * Get routeName
     *
     * @return string
     */
    public function getRouteName()
    {
        return $this->routeName;
    }
}

