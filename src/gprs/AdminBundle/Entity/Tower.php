<?php

namespace gprs\AdminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Tower
 */
class Tower
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $mcc;

    /**
     * @var integer
     */
    private $mnc;

    /**
     * @var integer
     */
    private $lac;

    /**
     * @var integer
     */
    private $cid;

    /**
     * @var float
     */
    private $lat;

    /**
     * @var float
     */
    private $lng;

    /**
     * @var \DateTime
     */
    private $created_at;


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
     * Set mcc
     *
     * @param integer $mcc
     * @return Tower
     */
    public function setMcc($mcc)
    {
        $this->mcc = $mcc;
    
        return $this;
    }

    /**
     * Get mcc
     *
     * @return integer 
     */
    public function getMcc()
    {
        return $this->mcc;
    }

    /**
     * Set mnc
     *
     * @param integer $mnc
     * @return Tower
     */
    public function setMnc($mnc)
    {
        $this->mnc = $mnc;
    
        return $this;
    }

    /**
     * Get mnc
     *
     * @return integer 
     */
    public function getMnc()
    {
        return $this->mnc;
    }

    /**
     * Set lac
     *
     * @param integer $lac
     * @return Tower
     */
    public function setLac($lac)
    {
        $this->lac = $lac;
    
        return $this;
    }

    /**
     * Get lac
     *
     * @return integer 
     */
    public function getLac()
    {
        return $this->lac;
    }

    /**
     * Set cid
     *
     * @param integer $cid
     * @return Tower
     */
    public function setCid($cid)
    {
        $this->cid = $cid;
    
        return $this;
    }

    /**
     * Get cid
     *
     * @return integer 
     */
    public function getCid()
    {
        return $this->cid;
    }

    /**
     * Set lat
     *
     * @param float $lat
     * @return Tower
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
     * @return Tower
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
     * Set created_at
     *
     * @param \DateTime $createdAt
     * @return Tower
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
     * @ORM\PrePersist
     */
    public function setCreatedAtValue()
    {
        // Add your code here
    }
}