<?php

namespace gprs\AdminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Googlecoord
 */
class Googlecoord
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $sms;

    /**
     * @var float
     */
    private $lat;

    /**
     * @var float
     */
    private $lng;

    /**
     * @var integer
     */
    private $status;

    /**
     * @var \DateTime
     */
    private $created_at;

    /**
     * @var \gprs\AdminBundle\Entity\Icebox
     */
    private $icebox;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set sms
     *
     * @param integer $sms
     * @return Googlecoord
     */
    public function setSms($sms)
    {
        $this->sms = $sms;
    
        return $this;
    }

    /**
     * Get sms
     *
     * @return integer 
     */
    public function getSms()
    {
        return $this->sms;
    }

    /**
     * Set lat
     *
     * @param float $lat
     * @return Googlecoord
     */
    public function setLat($lat)
    {
        $this->lat = $lat;
    
        return $this;
    }

    /**
     * Get lat
     *
     * @return float 
     */
    public function getLat()
    {
        return $this->lat;
    }

    /**
     * Set lng
     *
     * @param float $lng
     * @return Googlecoord
     */
    public function setLng($lng)
    {
        $this->lng = $lng;
    
        return $this;
    }

    /**
     * Get lng
     *
     * @return float 
     */
    public function getLng()
    {
        return $this->lng;
    }

    /**
     * Set status
     *
     * @param integer $status
     * @return Googlecoord
     */
    public function setStatus($status)
    {
        $this->status = $status;
    
        return $this;
    }

    /**
     * Get status
     *
     * @return integer 
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set created_at
     *
     * @param \DateTime $createdAt
     * @return Googlecoord
     */
    public function setCreatedAt($createdAt)
    {
        $this->created_at = $createdAt;
    
        return $this;
    }

    /**
     * Get created_at
     *
     * @return \DateTime 
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * Set icebox
     *
     * @param \gprs\AdminBundle\Entity\Icebox $icebox
     * @return Googlecoord
     */
    public function setIcebox(\gprs\AdminBundle\Entity\Icebox $icebox = null)
    {
        $this->icebox = $icebox;
    
        return $this;
    }

    /**
     * Get icebox
     *
     * @return \gprs\AdminBundle\Entity\Icebox 
     */
    public function getIcebox()
    {
        return $this->icebox;
    }
    /**
     * @ORM\PrePersist
     */
    public function setCreatedAtValue()
    {
        if(!$this->getCreatedAt())
        {
          $this->created_at = new \DateTime();
        }
    }
    /**
     * @var \DateTime
     */
    private $updated_at;

    /**
     * @var integer
     */
    private $count_updates;


    /**
     * Set updated_at
     *
     * @param \DateTime $updatedAt
     * @return Googlecoord
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updated_at = $updatedAt;
    
        return $this;
    }

    /**
     * Get updated_at
     *
     * @return \DateTime 
     */
    public function getUpdatedAt()
    {
        return $this->updated_at;
    }

    /**
     * Set count_updates
     *
     * @param integer $countUpdates
     * @return Googlecoord
     */
    public function setCountUpdates($countUpdates)
    {
        $this->count_updates = $countUpdates;
    
        return $this;
    }

    /**
     * Get count_updates
     *
     * @return integer 
     */
    public function getCountUpdates()
    {
        return $this->count_updates;
    }
    /**
     * @ORM\PrePersist
     */
    public function setUpdatedAtValue()
    {
        $this->updated_at = new \DateTime();
    }
}