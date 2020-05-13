<?php

namespace gprs\AdminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Alarm
 */
class Alarm
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $icebox_id;

    /**
     * @var \DateTime
     */
    private $created_at;

    /**
     * @var \DateTime
     */
    private $solved_at;

    /**
     * @var integer
     */
    private $dooropen;

    /**
     * @var boolean
     */
    private $location;

    /**
     * @var integer
     */
    private $temperatura;

    /**
     * @var integer
     */
    private $weight;

    /**
     * @var boolean
     */
    private $power;

    /**
     * @var integer
     */
    private $p1;

    /**
     * @var integer
     */
    private $p2;

    /**
     * @var integer
     */
    private $p3;

    /**
     * @var integer
     */
    private $p4;

    /**
     * @var integer
     */
    private $status;

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
     * Set icebox_id
     *
     * @param integer $iceboxId
     * @return Alarm
     */
    public function setIceboxId($iceboxId)
    {
        $this->icebox_id = $iceboxId;
    
        return $this;
    }

    /**
     * Get icebox_id
     *
     * @return integer 
     */
    public function getIceboxId()
    {
        return $this->icebox_id;
    }

    /**
     * Set created_at
     *
     * @param \DateTime $createdAt
     * @return Alarm
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
     * Set solved_at
     *
     * @param \DateTime $solvedAt
     * @return Alarm
     */
    public function setSolvedAt($solvedAt)
    {
        $this->solved_at = $solvedAt;
    
        return $this;
    }

    /**
     * Get solved_at
     *
     * @return \DateTime 
     */
    public function getSolvedAt()
    {
        return $this->solved_at;
    }

    /**
     * Set dooropen
     *
     * @param integer $dooropen
     * @return Alarm
     */
    public function setDooropen($dooropen)
    {
        $this->dooropen = $dooropen;
    
        return $this;
    }

    /**
     * Get dooropen
     *
     * @return integer 
     */
    public function getDooropen()
    {
        return $this->dooropen;
    }

    /**
     * Set location
     *
     * @param boolean $location
     * @return Alarm
     */
    public function setLocation($location)
    {
        $this->location = $location;
    
        return $this;
    }

    /**
     * Get location
     *
     * @return boolean 
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * Set temperatura
     *
     * @param integer $temperatura
     * @return Alarm
     */
    public function setTemperatura($temperatura)
    {
        $this->temperatura = $temperatura;
    
        return $this;
    }

    /**
     * Get temperatura
     *
     * @return integer 
     */
    public function getTemperatura()
    {
        return $this->temperatura;
    }

    /**
     * Set weight
     *
     * @param integer $weight
     * @return Alarm
     */
    public function setWeight($weight)
    {
        $this->weight = $weight;
    
        return $this;
    }

    /**
     * Get weight
     *
     * @return integer 
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * Set power
     *
     * @param boolean $power
     * @return Alarm
     */
    public function setPower($power)
    {
        $this->power = $power;
    
        return $this;
    }

    /**
     * Get power
     *
     * @return boolean 
     */
    public function getPower()
    {
        return $this->power;
    }

    /**
     * Set p1
     *
     * @param integer $p1
     * @return Alarm
     */
    public function setP1($p1)
    {
        $this->p1 = $p1;
    
        return $this;
    }

    /**
     * Get p1
     *
     * @return integer 
     */
    public function getP1()
    {
        return $this->p1;
    }

    /**
     * Set p2
     *
     * @param integer $p2
     * @return Alarm
     */
    public function setP2($p2)
    {
        $this->p2 = $p2;
    
        return $this;
    }

    /**
     * Get p2
     *
     * @return integer 
     */
    public function getP2()
    {
        return $this->p2;
    }

    /**
     * Set p3
     *
     * @param integer $p3
     * @return Alarm
     */
    public function setP3($p3)
    {
        $this->p3 = $p3;
    
        return $this;
    }

    /**
     * Get p3
     *
     * @return integer 
     */
    public function getP3()
    {
        return $this->p3;
    }

    /**
     * Set p4
     *
     * @param integer $p4
     * @return Alarm
     */
    public function setP4($p4)
    {
        $this->p4 = $p4;
    
        return $this;
    }

    /**
     * Get p4
     *
     * @return integer 
     */
    public function getP4()
    {
        return $this->p4;
    }

    /**
     * Set status
     *
     * @param integer $status
     * @return Alarm
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
     * Set icebox
     *
     * @param \gprs\AdminBundle\Entity\Icebox $icebox
     * @return Alarm
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
     * Set updated_at
     *
     * @param \DateTime $updatedAt
     * @return Alarm
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
     * @ORM\PreUpdate
     */
    public function setUpdatedAtValue()
    {
        $this->updated_at = new \DateTime();
    }
    /**
     * @var integer
     */
    private $p5;

    /**
     * @var integer
     */
    private $p6;


    /**
     * Set p5
     *
     * @param integer $p5
     * @return Alarm
     */
    public function setP5($p5)
    {
        $this->p5 = $p5;
    
        return $this;
    }

    /**
     * Get p5
     *
     * @return integer 
     */
    public function getP5()
    {
        return $this->p5;
    }

    /**
     * Set p6
     *
     * @param integer $p6
     * @return Alarm
     */
    public function setP6($p6)
    {
        $this->p6 = $p6;
    
        return $this;
    }

    /**
     * Get p6
     *
     * @return integer 
     */
    public function getP6()
    {
        return $this->p6;
    }
}