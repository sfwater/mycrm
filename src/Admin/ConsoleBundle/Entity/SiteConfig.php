<?php

namespace Admin\ConsoleBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SiteConfig
 *
 * @ORM\Table(name="site_config")
 * @ORM\Entity(repositoryClass="Admin\ConsoleBundle\Repository\SiteConfigRepository")
 */
class SiteConfig
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
     * @ORM\Column(name="config", type="text")
     */
    private $config;


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
     * Set config
     *
     * @param string $config
     *
     * @return SiteConfig
     */
    public function setConfig($config)
    {
        $this->config = $config;

        return $this;
    }

    /**
     * Get config
     *
     * @return string
     */
    public function getConfig()
    {
        return $this->config;
    }
}

