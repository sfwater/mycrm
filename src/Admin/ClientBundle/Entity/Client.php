<?php

namespace Admin\ClientBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Client
 *
 * @ORM\Table(name="clients")
 * @ORM\Entity(repositoryClass="Admin\ClientBundle\Repository\ClientRepository")
 */
class Client
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
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="address", type="string", length=255)
     */
    private $address;

    /**
     * @var string
     *
     * @ORM\Column(name="contactor", type="string", length=255)
     */
    private $contactor;

    /**
     * @var string
     *
     * @ORM\Column(name="contact", type="string", length=255)
     */
    private $contact;

    /**
     * @var string
     *
     * @ORM\Column(name="note", type="text", nullable=true)
     */
    private $note;

    /**
     * @var int
     *
     * @ORM\Column(name="status", type="integer")
     */
    private $status;

    /**
     * @var int
     *
     * @ORM\Column(name="wtime", type="integer", nullable=true)
     */
    private $wtime;

    /**
     * @var int
     *
     * @ORM\Column(name="ctime", type="integer")
     */
    private $ctime;

    /**
     * @var int
     *
     * @ORM\Column(name="outtime", type="integer", nullable=true)
     */
    private $outtime;

    /**
    * @ORM\OneToMany(targetEntity="ClientAccessRecord", mappedBy="client")
    */
    private $records;

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
     * Set name
     *
     * @param string $name
     *
     * @return Client
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set address
     *
     * @param string $address
     *
     * @return Client
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get address
     *
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set contactor
     *
     * @param string $contactor
     *
     * @return Client
     */
    public function setContactor($contactor)
    {
        $this->contactor = $contactor;

        return $this;
    }

    /**
     * Get contactor
     *
     * @return string
     */
    public function getContactor()
    {
        return $this->contactor;
    }

    /**
     * Set contact
     *
     * @param string $contact
     *
     * @return Client
     */
    public function setContact($contact)
    {
        $this->contact = $contact;

        return $this;
    }

    /**
     * Get contact
     *
     * @return string
     */
    public function getContact()
    {
        return $this->contact;
    }

    /**
     * Set note
     *
     * @param string $note
     *
     * @return Client
     */
    public function setNote($note)
    {
        $this->note = $note;

        return $this;
    }

    /**
     * Get note
     *
     * @return string
     */
    public function getNote()
    {
        return $this->note;
    }

    /**
     * Set status
     *
     * @param integer $status
     *
     * @return Client
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set wtime
     *
     * @param integer $wtime
     *
     * @return Client
     */
    public function setWtime($wtime)
    {
        $this->wtime = $wtime;

        return $this;
    }

    /**
     * Get wtime
     *
     * @return int
     */
    public function getWtime()
    {
        return $this->wtime;
    }

    /**
     * Set ctime
     *
     * @param integer $ctime
     *
     * @return Client
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
     * Constructor
     */
    public function __construct()
    {
        $this->records = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add record
     *
     * @param \Admin\ClientBundle\Entity\ClientAccessRecord $record
     *
     * @return Client
     */
    public function addRecord(\Admin\ClientBundle\Entity\ClientAccessRecord $record)
    {
        $this->records[] = $record;

        return $this;
    }

    /**
     * Remove record
     *
     * @param \Admin\ClientBundle\Entity\ClientAccessRecord $record
     */
    public function removeRecord(\Admin\ClientBundle\Entity\ClientAccessRecord $record)
    {
        $this->records->removeElement($record);
    }

    /**
     * Get records
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getRecords()
    {
        return $this->records;
    }

    /**
     * Set outtime
     *
     * @param integer $outtime
     *
     * @return Client
     */
    public function setOuttime($outtime)
    {
        $this->outtime = $outtime;

        return $this;
    }

    /**
     * Get outtime
     *
     * @return integer
     */
    public function getOuttime()
    {
        return $this->outtime;
    }
}
