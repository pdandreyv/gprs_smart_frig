<?php

namespace gprs\AdminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Temperature
 */
class Temperature
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
    private $t_out;

    /**
     * @var float
     */
    private $t_inside;

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
     * Set icebox_id
     *
     * @param integer $iceboxId
     * @return Temperature
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
     * Set t_out
     *
     * @param float $tOut
     * @return Temperature
     */
    public function setTOut($tOut)
    {
        $this->t_out = $tOut;
    
        return $this;
    }

    /**
     * Get t_out
     *
     * @return float 
     */
    public function getTOut()
    {
        return $this->t_out;
    }

    /**
     * Set t_inside
     *
     * @param float $tInside
     * @return Temperature
     */
    public function setTInside($tInside)
    {
        $this->t_inside = $tInside;
    
        return $this;
    }

    /**
     * Get t_inside
     *
     * @return float 
     */
    public function getTInside()
    {
        return $this->t_inside;
    }

    /**
     * Set created_at
     *
     * @param \DateTime $createdAt
     * @return Temperature
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
     * @return Temperature
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
}