<?php

namespace gprs\AdminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Location
 */
class Location
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
     * @var float
     */
    private $lat;

    /**
     * @var float
     */
    private $lng;

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
     * @return Location
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
     * Set lat
     *
     * @param float $lat
     * @return Location
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
     * @return Location
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
     * Set icebox
     *
     * @param \gprs\AdminBundle\Entity\Icebox $icebox
     * @return Location
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
     * @var \DateTime
     */
    private $created_at;


    /**
     * Set created_at
     *
     * @param \DateTime $createdAt
     * @return Location
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
        if(!$this->getCreatedAt())
        {
          $this->created_at = new \DateTime();
        }
    }
    
    public function getMethodName($name)
    {
        return implode('',array_map('ucfirst',explode('_',$name)));
    }
    
    public function toArray($exclude = array()){
        $class_vars = get_class_vars(get_class($this));
        $res = array();
        
        foreach ($class_vars as $name => $value) {
            $method = 'get'.$this->getMethodName($name);

            if(in_array($name, $exclude)){
                continue;
            }
            
            switch ($name) {
                case 'created_at':
                    if($this->$method() instanceof \DateTime){
                        $res[$name] = $this->$method()->format('Y-m-d H:i:s');
                    }else{
                        $res[$name] = '';
                    }
                    break;
                default:
                    $res[$name] = $this->$method();
                    break;
            }
        }
        
        return $res;
    }
    /**
     * @var integer
     */
    private $radius;


    /**
     * Set radius
     *
     * @param integer $radius
     * @return Location
     */
    public function setRadius($radius)
    {
        $this->radius = $radius;
    
        return $this;
    }

    /**
     * Get radius
     *
     * @return integer 
     */
    public function getRadius()
    {
        return $this->radius;
    }
}