<?php

namespace App\Document\Configuration;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/** @MongoDB\EmbeddedDocument */
class ConfigurationOsm
{
    /**
     * OSM host URL
     *
     * @MongoDB\Field(type="string")
     */
    public $osmHost = "https://www.openstreetmap.org";

    /**
     * OSM user name for instance account
     *
     * @MongoDB\Field(type="string")
     */
    public $osmUsername;

    /**
     * OSM password for instance account
     *
     * @MongoDB\Field(type="string")
     */
    public $osmPassword;

    /**
     * Set osmHost.
     *
     * @param string $osmHost
     *
     * @return $this
     */
    public function setOsmHost($osmHost)
    {
        $this->osmHost = $osmHost;

        return $this;
    }

    /**
     * Get osmHost.
     *
     * @return string $osmHost
     */
    public function getOsmHost()
    {
        return $this->osmHost;
    }

    /**
     * Set osmUsername.
     *
     * @param string $osmUsername
     *
     * @return $this
     */
    public function setOsmUsername($osmUsername)
    {
        $this->osmUsername = $osmUsername;

        return $this;
    }

    /**
     * Get osmUsername.
     *
     * @return string $osmUsername
     */
    public function getOsmUsername()
    {
        return $this->osmUsername;
    }

    /**
     * Set osmPassword.
     *
     * @param string $osmPassword
     *
     * @return $this
     */
    public function setOsmPassword($osmPassword)
    {
        $this->osmPassword = $osmPassword;

        return $this;
    }

    /**
     * Get osmPassword.
     *
     * @return string $osmPassword
     */
    public function getOsmPassword()
    {
        return $this->osmPassword;
    }
}