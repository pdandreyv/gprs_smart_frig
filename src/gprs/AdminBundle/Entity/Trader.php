<?php

namespace gprs\AdminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Trader
 */
class Trader
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $fio;

    /**
     * @var string
     */
    private $phone;

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
     * Set fio
     *
     * @param string $fio
     * @return Trader
     */
    public function setFio($fio)
    {
        $this->fio = $fio;
    
        return $this;
    }

    /**
     * Get fio
     *
     * @return string 
     */
    public function getFio()
    {
        return $this->fio;
    }

    /**
     * Set phone
     *
     * @param string $phone
     * @return Trader
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
    
        return $this;
    }

    /**
     * Get phone
     *
     * @return string 
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set created_at
     *
     * @param \DateTime $createdAt
     * @return Trader
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
    
    public function __toString()
    {
        return (string)$this->id;
    }
    /**
     * @var string
     */
    private $position;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $address;

    /**
     * @var boolean
     */
    private $alarm_weight;

    /**
     * @var boolean
     */
    private $alarm_location;

    /**
     * @var boolean
     */
    private $alarm_temperature;


    /**
     * Set position
     *
     * @param string $position
     * @return Trader
     */
    public function setPosition($position)
    {
        $this->position = $position;
    
        return $this;
    }

    /**
     * Get position
     *
     * @return string 
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return Trader
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
     * Set address
     *
     * @param string $address
     * @return Trader
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
     * Set alarm_weight
     *
     * @param boolean $alarmWeight
     * @return Trader
     */
    public function setAlarmWeight($alarmWeight)
    {
        $this->alarm_weight = $alarmWeight;
    
        return $this;
    }

    /**
     * Get alarm_weight
     *
     * @return boolean 
     */
    public function getAlarmWeight()
    {
        return $this->alarm_weight;
    }

    /**
     * Set alarm_location
     *
     * @param boolean $alarmLocation
     * @return Trader
     */
    public function setAlarmLocation($alarmLocation)
    {
        $this->alarm_location = $alarmLocation;
    
        return $this;
    }

    /**
     * Get alarm_location
     *
     * @return boolean 
     */
    public function getAlarmLocation()
    {
        return $this->alarm_location;
    }

    /**
     * Set alarm_temperature
     *
     * @param boolean $alarmTemperature
     * @return Trader
     */
    public function setAlarmTemperature($alarmTemperature)
    {
        $this->alarm_temperature = $alarmTemperature;
    
        return $this;
    }

    /**
     * Get alarm_temperature
     *
     * @return boolean 
     */
    public function getAlarmTemperature()
    {
        return $this->alarm_temperature;
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
     * @var string
     */
    private $email;


    /**
     * Set email
     *
     * @param string $email
     * @return Trader
     */
    public function setEmail($email)
    {
        $this->email = $email;
    
        return $this;
    }

    /**
     * Get email
     *
     * @return string 
     */
    public function getEmail()
    {
        return $this->email;
    }
    /**
     * @var boolean
     */
    private $alarm_power;


    /**
     * Set alarm_power
     *
     * @param boolean $alarmPower
     * @return Trader
     */
    public function setAlarmPower($alarmPower)
    {
        $this->alarm_power = $alarmPower;
    
        return $this;
    }

    /**
     * Get alarm_power
     *
     * @return boolean 
     */
    public function getAlarmPower()
    {
        return $this->alarm_power;
    }
}