<?php

namespace gprs\AdminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Data
 */
class Data
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
     * @var integer
     */
    private $net_rssi;

    /**
     * @var integer
     */
    private $net_srv_cid;

    /**
     * @var string
     */
    private $net_cids;

    /**
     * @var integer
     */
    private $pin1_count;

    /**
     * @var integer
     */
    private $pin1_uptime;

    /**
     * @var integer
     */
    private $adc;

    /**
     * @var integer
     */
    private $temp;

    /**
     * @var integer
     */
    private $bat;

    /**
     * @var integer
     */
    private $par1;

    /**
     * @var integer
     */
    private $par2;

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
    private $t_out;

    /**
     * @var integer
     */
    private $t_inside;

    /**
     * @var integer
     */
    private $weight;

    /**
     * @var integer
     */
    private $weight_status;

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
     * @return Data
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
     * Set net_rssi
     *
     * @param integer $netRssi
     * @return Data
     */
    public function setNetRssi($netRssi)
    {
        $this->net_rssi = $netRssi;
    
        return $this;
    }

    /**
     * Get net_rssi
     *
     * @return integer 
     */
    public function getNetRssi()
    {
        return $this->net_rssi;
    }

    /**
     * Set net_srv_cid
     *
     * @param integer $netSrvCid
     * @return Data
     */
    public function setNetSrvCid($netSrvCid)
    {
        $this->net_srv_cid = $netSrvCid;
    
        return $this;
    }

    /**
     * Get net_srv_cid
     *
     * @return integer 
     */
    public function getNetSrvCid()
    {
        return $this->net_srv_cid;
    }

    /**
     * Set net_cids
     *
     * @param string $netCids
     * @return Data
     */
    public function setNetCids($netCids)
    {
        $this->net_cids = $netCids;
    
        return $this;
    }

    /**
     * Get net_cids
     *
     * @return string 
     */
    public function getNetCids()
    {
        return $this->net_cids;
    }

    /**
     * Set pin1_count
     *
     * @param integer $pin1Count
     * @return Data
     */
    public function setPin1Count($pin1Count)
    {
        $this->pin1_count = $pin1Count;
    
        return $this;
    }

    /**
     * Get pin1_count
     *
     * @return integer 
     */
    public function getPin1Count()
    {
        return $this->pin1_count;
    }

    /**
     * Set pin1_uptime
     *
     * @param integer $pin1Uptime
     * @return Data
     */
    public function setPin1Uptime($pin1Uptime)
    {
        $this->pin1_uptime = $pin1Uptime;
    
        return $this;
    }

    /**
     * Get pin1_uptime
     *
     * @return integer 
     */
    public function getPin1Uptime()
    {
        return $this->pin1_uptime;
    }

    /**
     * Set adc
     *
     * @param integer $adc
     * @return Data
     */
    public function setAdc($adc)
    {
        $this->adc = $adc;
    
        return $this;
    }

    /**
     * Get adc
     *
     * @return integer 
     */
    public function getAdc()
    {
        return $this->adc;
    }

    /**
     * Set temp
     *
     * @param integer $temp
     * @return Data
     */
    public function setTemp($temp)
    {
        $this->temp = $temp;
    
        return $this;
    }

    /**
     * Get temp
     *
     * @return integer 
     */
    public function getTemp()
    {
        return $this->temp;
    }

    /**
     * Set bat
     *
     * @param integer $bat
     * @return Data
     */
    public function setBat($bat)
    {
        $this->bat = $bat;
    
        return $this;
    }

    /**
     * Get bat
     *
     * @return integer 
     */
    public function getBat()
    {
        return $this->bat;
    }

    /**
     * Set par1
     *
     * @param integer $par1
     * @return Data
     */
    public function setPar1($par1)
    {
        $this->par1 = $par1;
    
        return $this;
    }

    /**
     * Get par1
     *
     * @return integer 
     */
    public function getPar1()
    {
        return $this->par1;
    }

    /**
     * Set par2
     *
     * @param integer $par2
     * @return Data
     */
    public function setPar2($par2)
    {
        $this->par2 = $par2;
    
        return $this;
    }

    /**
     * Get par2
     *
     * @return integer 
     */
    public function getPar2()
    {
        return $this->par2;
    }

    /**
     * Set dooropen
     *
     * @param integer $dooropen
     * @return Data
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
     * @return Data
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
     * Set t_out
     *
     * @param integer $tOut
     * @return Data
     */
    public function setTOut($tOut)
    {
        $this->t_out = $tOut;
    
        return $this;
    }

    /**
     * Get t_out
     *
     * @return integer 
     */
    public function getTOut()
    {
        return $this->t_out;
    }

    /**
     * Set t_inside
     *
     * @param integer $tInside
     * @return Data
     */
    public function setTInside($tInside)
    {
        $this->t_inside = $tInside;
    
        return $this;
    }

    /**
     * Get t_inside
     *
     * @return integer 
     */
    public function getTInside()
    {
        return $this->t_inside;
    }

    /**
     * Set weight
     *
     * @param integer $weight
     * @return Data
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
     * Set weight_status
     *
     * @param integer $weightStatus
     * @return Data
     */
    public function setWeightStatus($weightStatus)
    {
        $this->weight_status = $weightStatus;
    
        return $this;
    }

    /**
     * Get weight_status
     *
     * @return integer 
     */
    public function getWeightStatus()
    {
        return $this->weight_status;
    }

    /**
     * Set created_at
     *
     * @param \DateTime $createdAt
     * @return Data
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
     * @return Data
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
                case 'icebox':
                    $res[$name] = $this->getIceboxId();
                    break;
                default:
                    $res[$name] = $this->$method();
                    break;
            }
        }
        
        return $res;
    }
    
    public function __toString()
    {
        return (string)$this->id;
    }
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
     * Set p1
     *
     * @param integer $p1
     * @return Data
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
     * @return Data
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
     * @return Data
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
     * @return Data
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
     * @var integer
     */
    private $p1d;

    /**
     * @var integer
     */
    private $p2d;

    /**
     * @var integer
     */
    private $p3d;

    /**
     * @var integer
     */
    private $p4d;


    /**
     * Set p1d
     *
     * @param integer $p1d
     * @return Data
     */
    public function setP1d($p1d)
    {
        $this->p1d = $p1d;
    
        return $this;
    }

    /**
     * Get p1d
     *
     * @return integer 
     */
    public function getP1d()
    {
        return $this->p1d;
    }

    /**
     * Set p2d
     *
     * @param integer $p2d
     * @return Data
     */
    public function setP2d($p2d)
    {
        $this->p2d = $p2d;
    
        return $this;
    }

    /**
     * Get p2d
     *
     * @return integer 
     */
    public function getP2d()
    {
        return $this->p2d;
    }

    /**
     * Set p3d
     *
     * @param integer $p3d
     * @return Data
     */
    public function setP3d($p3d)
    {
        $this->p3d = $p3d;
    
        return $this;
    }

    /**
     * Get p3d
     *
     * @return integer 
     */
    public function getP3d()
    {
        return $this->p3d;
    }

    /**
     * Set p4d
     *
     * @param integer $p4d
     * @return Data
     */
    public function setP4d($p4d)
    {
        $this->p4d = $p4d;
    
        return $this;
    }

    /**
     * Get p4d
     *
     * @return integer 
     */
    public function getP4d()
    {
        return $this->p4d;
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
     * @var integer
     */
    private $p5d;

    /**
     * @var integer
     */
    private $p6d;


    /**
     * Set p5
     *
     * @param integer $p5
     * @return Data
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
     * @return Data
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

    /**
     * Set p5d
     *
     * @param integer $p5d
     * @return Data
     */
    public function setP5d($p5d)
    {
        $this->p5d = $p5d;
    
        return $this;
    }

    /**
     * Get p5d
     *
     * @return integer 
     */
    public function getP5d()
    {
        return $this->p5d;
    }

    /**
     * Set p6d
     *
     * @param integer $p6d
     * @return Data
     */
    public function setP6d($p6d)
    {
        $this->p6d = $p6d;
    
        return $this;
    }

    /**
     * Get p6d
     *
     * @return integer 
     */
    public function getP6d()
    {
        return $this->p6d;
    }
    /**
     * @var integer
     */
    private $p1c;

    /**
     * @var integer
     */
    private $p2c;

    /**
     * @var integer
     */
    private $p3c;

    /**
     * @var integer
     */
    private $p4c;

    /**
     * @var integer
     */
    private $p5c;

    /**
     * @var integer
     */
    private $p6c;


    /**
     * Set p1c
     *
     * @param integer $p1c
     * @return Data
     */
    public function setP1c($p1c)
    {
        $this->p1c = $p1c;
    
        return $this;
    }

    /**
     * Get p1c
     *
     * @return integer 
     */
    public function getP1c()
    {
        return $this->p1c;
    }

    /**
     * Set p2c
     *
     * @param integer $p2c
     * @return Data
     */
    public function setP2c($p2c)
    {
        $this->p2c = $p2c;
    
        return $this;
    }

    /**
     * Get p2c
     *
     * @return integer 
     */
    public function getP2c()
    {
        return $this->p2c;
    }

    /**
     * Set p3c
     *
     * @param integer $p3c
     * @return Data
     */
    public function setP3c($p3c)
    {
        $this->p3c = $p3c;
    
        return $this;
    }

    /**
     * Get p3c
     *
     * @return integer 
     */
    public function getP3c()
    {
        return $this->p3c;
    }

    /**
     * Set p4c
     *
     * @param integer $p4c
     * @return Data
     */
    public function setP4c($p4c)
    {
        $this->p4c = $p4c;
    
        return $this;
    }

    /**
     * Get p4c
     *
     * @return integer 
     */
    public function getP4c()
    {
        return $this->p4c;
    }

    /**
     * Set p5c
     *
     * @param integer $p5c
     * @return Data
     */
    public function setP5c($p5c)
    {
        $this->p5c = $p5c;
    
        return $this;
    }

    /**
     * Get p5c
     *
     * @return integer 
     */
    public function getP5c()
    {
        return $this->p5c;
    }

    /**
     * Set p6c
     *
     * @param integer $p6c
     * @return Data
     */
    public function setP6c($p6c)
    {
        $this->p6c = $p6c;
    
        return $this;
    }

    /**
     * Get p6c
     *
     * @return integer 
     */
    public function getP6c()
    {
        return $this->p6c;
    }
}