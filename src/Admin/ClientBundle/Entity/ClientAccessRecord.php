<?php

namespace Admin\ClientBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ClientAccessRecord
 *
 * @ORM\Table(name="client_access_records")
 * @ORM\Entity(repositoryClass="Admin\ClientBundle\Repository\ClientAccessRecordRepository")
 */
class ClientAccessRecord
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
     * @ORM\Column(name="description", type="text")
     */
    private $description;

    /**
     * @var int
     *
     * @ORM\Column(name="ctime", type="integer")
     */
    private $ctime;

    /**
    * @ORM\ManyToOne(targetEntity="Client", inversedBy="records")
    */
    private $client;

    /**
    * @ORM\ManyToOne(targetEntity="Admin\UserBundle\Entity\User")
    */
    private $user;

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
     * Set description
     *
     * @param string $description
     *
     * @return ClientAccessRecord
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set ctime
     *
     * @param integer $ctime
     *
     * @return ClientAccessRecord
     */
    public function setCtime($ctime)
    {
        $this->ctime = $ctime;

        return $this;
    }

    /**
     * Get ctime
     *
     * @return int
     */
    public function getCtime()
    {
        return $this->ctime;
    }

    /**
     * Set client
     *
     * @param \Admin\ClientBundle\Entity\Client $client
     *
     * @return ClientAccessRecord
     */
    public function setClient(\Admin\ClientBundle\Entity\Client $client = null)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * Get client
     *
     * @return \Admin\ClientBundle\Entity\Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Set user
     *
     * @param \Admin\UserBundle\Entity\User $user
     *
     * @return ClientAccessRecord
     */
    public function setUser(\Admin\UserBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \Admin\UserBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }
}
