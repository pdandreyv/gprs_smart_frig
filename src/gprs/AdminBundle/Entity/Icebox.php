<?php

namespace gprs\AdminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\EntityManager;

/**
 * Icebox
 */
class Icebox
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $serial_number;

    /**
     * @var string
     */
    private $model;

    /**
     * @var string
     */
    private $producer;

    /**
     * @var \DateTime
     */
    private $date_producted;

    /**
     * @var string
     */
    private $image;

    /**
     * @var string
     */
    private $country;

    /**
     * @var string
     */
    private $region;

    /**
     * @var string
     */
    private $city;

    /**
     * @var string
     */
    private $contragent;

    /**
     * @var boolean
     */
    private $status;

   
    public function __construct($params=array())
    {
        if ($params){
            $this->fromArray($params);
        }
    }

    public static function getStatuses() {
        return array(
            '0'=>'',
            '1'=>'Not active',
            '2'=>'Active',
            '3'=>'Coordinates was changed',
            '4'=>'Weith is light',
            '5'=>'Temperature is high',
            '6'=>'Device is removed',
            '7'=>'Device is removed',
        );
    }
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
     * Set serial_number
     *
     * @param string $serialNumber
     * @return Icebox
     */
    public function setSerialNumber($serialNumber)
    {
        $this->serial_number = $serialNumber;
    
        return $this;
    }

    /**
     * Get serial_number
     *
     * @return string 
     */
    public function getSerialNumber()
    {
        return $this->serial_number;
    }

    /**
     * Set model
     *
     * @param string $model
     * @return Icebox
     */
    public function setModel($model)
    {
        $this->model = $model;
    
        return $this;
    }

    /**
     * Get model
     *
     * @return string 
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Set producer
     *
     * @param string $producer
     * @return Icebox
     */
    public function setProducer($producer)
    {
        $this->producer = $producer;
    
        return $this;
    }

    /**
     * Get producer
     *
     * @return string 
     */
    public function getProducer()
    {
        return $this->producer;
    }

    /**
     * Set date_producted
     *
     * @param \DateTime $dateProducted
     * @return Icebox
     */
    public function setDateProducted($dateProducted)
    {
        $this->date_producted = $dateProducted;
    
        return $this;
    }

    /**
     * Get date_producted
     *
     * @return \DateTime 
     */
    public function getDateProducted()
    {
        return $this->date_producted;
    }

    /**
     * Set image
     *
     * @param string $image
     * @return Icebox
     */
    public function setImage($image)
    {
        $this->image = $image;
    
        return $this;
    }

    /**
     * Get image
     *
     * @return string 
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Set country
     *
     * @param string $country
     * @return Icebox
     */
    public function setCountry($country)
    {
        $this->country = $country;
    
        return $this;
    }

    /**
     * Get country
     *
     * @return string 
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Set region
     *
     * @param string $region
     * @return Icebox
     */
    public function setRegion($region)
    {
        $this->region = $region;
    
        return $this;
    }

    /**
     * Get region
     *
     * @return string 
     */
    public function getRegion()
    {
        return $this->region;
    }

    /**
     * Set city
     *
     * @param string $city
     * @return Icebox
     */
    public function setCity($city)
    {
        $this->city = $city;
    
        return $this;
    }

    /**
     * Get city
     *
     * @return string 
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set contragent
     *
     * @param string $contragent
     * @return Icebox
     */
    public function setContragent($contragent)
    {
        $this->contragent = $contragent;
    
        return $this;
    }

    /**
     * Get contragent
     *
     * @return string 
     */
    public function getContragent()
    {
        return $this->contragent;
    }

    /**
     * Set status
     *
     * @param boolean $status
     * @return Icebox
     */
    public function setStatus($status)
    {
        $this->status = $status;
    
        return $this;
    }

    /**
     * Get status
     *
     * @return boolean 
     */
    public function getStatus()
    {
        return $this->status;
    }
    
    public function fromArray($params)
    {
        foreach($params as $key => $val)
        {
            if($key=='created_at'){
                if($val)
                    $val = new \DateTime();
            }
            if($val) {
                $method = 'set'.$this->getMethodName($key);
                $this->$method($val);
            }
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
//                case '__isInitialized__':
//                    break;
//                case 'collection':
//                    if(is_object($this->$method())){
//                        $res[$name] = $this->$method()->toArray(array('user', 'item'));
//                    }
//                    break;
//                case 'productVariant':
//                    if(is_object($this->$method())){
//                        $res[$name] = $this->$method()->toArray(array('product'));
//                    }
//                    break;
                case 'date_producted':
                    if($this->$method() instanceof \DateTime){
                        $res[$name] = $this->$method()->format('Y-m-d');
                    }else{
                        $res[$name] = '';
                    }
                    break;
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
    private $lat;

    /**
     * @var integer
     */
    private $lng;


    /**
     * Set lat
     *
     * @param integer $lat
     * @return Icebox
     */
    public function setLat($lat)
    {
        $this->lat = $lat;
    
        return $this;
    }

    /**
     * Get lat
     *
     * @return integer 
     */
    public function getLat()
    {
        return $this->lat;
    }

    /**
     * Set lng
     *
     * @param integer $lng
     * @return Icebox
     */
    public function setLng($lng)
    {
        $this->lng = $lng;
    
        return $this;
    }

    /**
     * Get lng
     *
     * @return integer 
     */
    public function getLng()
    {
        return $this->lng;
    }
    /**
     * @var string
     */
    private $address;


    /**
     * Set address
     *
     * @param string $address
     * @return Icebox
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
     * @var string
     */
    private $phone;


    /**
     * Set phone
     *
     * @param string $phone
     * @return Icebox
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
     * @var \DateTime
     */
    private $created_at;


    /**
     * Set created_at
     *
     * @param \DateTime $createdAt
     * @return Icebox
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
    /**
     * @var \DateTime
     */
    private $updated_at;


    /**
     * Set updated_at
     *
     * @param \DateTime $updatedAt
     * @return Icebox
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
     * @ORM\PrePersist
     */
    public function setUpdatedAtValue()
    {
        $this->updated_at = new \DateTime();
    }
    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $type;


    /**
     * Set title
     *
     * @param string $title
     * @return Icebox
     */
    public function setTitle($title)
    {
        $this->title = $title;
    
        return $this;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return Icebox
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
     * Set type
     *
     * @param string $type
     * @return Icebox
     */
    public function setType($type)
    {
        $this->type = $type;
    
        return $this;
    }

    /**
     * Get type
     *
     * @return string 
     */
    public function getType()
    {
        return $this->type;
    }
    /**
     * @var string
     */
    private $manager;


    /**
     * Set manager
     *
     * @param string $manager
     * @return Icebox
     */
    public function setManager($manager)
    {
        $this->manager = $manager;
    
        return $this;
    }

    /**
     * Get manager
     *
     * @return string 
     */
    public function getManager()
    {
        return $this->manager;
    }
    /**
     * @var integer
     */
    private $outlet_id;

    /**
     * @var integer
     */
    private $trader_id;

    /**
     * @var string
     */
    private $monitor;


    /**
     * Set outlet_id
     *
     * @param integer $outletId
     * @return Icebox
     */
    public function setOutletId($outletId)
    {
        $this->outlet_id = $outletId;
    
        return $this;
    }

    /**
     * Get outlet_id
     *
     * @return integer 
     */
    public function getOutletId()
    {
        return $this->outlet_id;
    }

    /**
     * Set trader_id
     *
     * @param integer $traderId
     * @return Icebox
     */
    public function setTraderId($traderId)
    {
        $this->trader_id = $traderId;
    
        return $this;
    }

    /**
     * Get trader_id
     *
     * @return integer 
     */
    public function getTraderId()
    {
        return $this->trader_id;
    }

    /**
     * Set monitor
     *
     * @param string $monitor
     * @return Icebox
     */
    public function setMonitor($monitor)
    {
        $this->monitor = $monitor;
    
        return $this;
    }

    /**
     * Get monitor
     *
     * @return string 
     */
    public function getMonitor()
    {
        return $this->monitor;
    }
    /**
     * @var integer
     */
    private $iccid;


    /**
     * Set iccid
     *
     * @param integer $iccid
     * @return Icebox
     */
    public function setIccid($iccid)
    {
        $this->iccid = $iccid;
    
        return $this;
    }

    /**
     * Get iccid
     *
     * @return integer 
     */
    public function getIccid()
    {
        return $this->iccid;
    }
    /**
     * @var \gprs\AdminBundle\Entity\Trader
     */
    private $trader;

    /**
     * @var \gprs\AdminBundle\Entity\Outlet
     */
    private $outlet;


    /**
     * Set trader
     *
     * @param \gprs\AdminBundle\Entity\Trader $trader
     * @return Icebox
     */
    public function setTrader(\gprs\AdminBundle\Entity\Trader $trader = null)
    {
        $this->trader = $trader;
    
        return $this;
    }

    /**
     * Get trader
     *
     * @return \gprs\AdminBundle\Entity\Trader 
     */
    public function getTrader()
    {
        return $this->trader;
    }

    /**
     * Set outlet
     *
     * @param \gprs\AdminBundle\Entity\Outlet $outlet
     * @return Icebox
     */
    public function setOutlet(\gprs\AdminBundle\Entity\Outlet $outlet = null)
    {
        $this->outlet = $outlet;
    
        return $this;
    }

    /**
     * Get outlet
     *
     * @return \gprs\AdminBundle\Entity\Outlet 
     */
    public function getOutlet()
    {
        return $this->outlet;
    }

    /**
     * @var string
     */
    private $imei;

    /**
     * @var string
     */
    private $calib_temp;

    /**
     * @var string
     */
    private $calib_weight;


    /**
     * Set imei
     *
     * @param string $imei
     * @return Icebox
     */
    public function setImei($imei)
    {
        $this->imei = $imei;
    
        return $this;
    }

    /**
     * Get imei
     *
     * @return string 
     */
    public function getImei()
    {
        return $this->imei;
    }

    /**
     * Set calib_temp
     *
     * @param string $calibTemp
     * @return Icebox
     */
    public function setCalibTemp($calibTemp)
    {
        $this->calib_temp = $calibTemp;
    
        return $this;
    }

    /**
     * Get calib_temp
     *
     * @return string 
     */
    public function getCalibTemp()
    {
        return $this->calib_temp;
    }

    /**
     * Set calib_weight
     *
     * @param string $calibWeight
     * @return Icebox
     */
    public function setCalibWeight($calibWeight)
    {
        $this->calib_weight = $calibWeight;
    
        return $this;
    }

    /**
     * Get calib_weight
     *
     * @return string 
     */
    public function getCalibWeight()
    {
        return $this->calib_weight;
    }
    /**
     * @var string
     */
    private $code_tt;


    /**
     * Set code_tt
     *
     * @param string $codeTt
     * @return Icebox
     */
    public function setCodeTt($codeTt)
    {
        $this->code_tt = $codeTt;
    
        return $this;
    }

    /**
     * Get code_tt
     *
     * @return string 
     */
    public function getCodeTt()
    {
        return $this->code_tt;
    }
    /**
     * @var integer
     */
    private $order;


    /**
     * Set order
     *
     * @param integer $order
     * @return Icebox
     */
    public function setOrder($order)
    {
        $this->order = $order;
    
        return $this;
    }

    /**
     * Get order
     *
     * @return integer 
     */
    public function getOrder()
    {
        return $this->order;
    }
    
    static function saveIccid($iccid)
    {
        $a = self::loadIccid();
        if(!in_array($iccid."\n", $a)){
            $f = fopen('iccids.txt', 'a');
            fwrite($f, $iccid."\n");
            fclose($f);
        }
        //var_dump($a); exit;
    }
    static function loadIccid()
    {
        $filename = "iccids.txt";
        if(file_exists($filename)){
            return file($filename);
        }
        return array();
    }
    static function delIccid($iccid)
    {
        $filename = "iccids.txt";
        if(file_exists($filename)){
            $a = file($filename);
            $f = fopen('iccids.txt', 'w');
            foreach($a as $s){
                if(trim($s) != $iccid) fwrite($f, $s);
            }
            fclose($f);
        }
        //var_dump(file($filename));
    }
    /**
     * @var integer
     */
    private $ordering;


    /**
     * Set ordering
     *
     * @param integer $ordering
     * @return Icebox
     */
    public function setOrdering($ordering)
    {
        $this->ordering = $ordering;
    
        return $this;
    }

    /**
     * Get ordering
     *
     * @return integer 
     */
    public function getOrdering()
    {
        return $this->ordering;
    }
    
    public function __toString()
    {
        return (string)$this->id;
    }

    /**
     * @var string
     */
    private $passwod;

    /**
     * @var string
     */
    private $command;


    /**
     * Set passwod
     *
     * @param string $passwod
     * @return Icebox
     */
    public function setPasswod($passwod)
    {
        $this->passwod = $passwod;
    
        return $this;
    }

    /**
     * Get passwod
     *
     * @return string 
     */
    public function getPasswod()
    {
        return $this->passwod;
    }

    /**
     * Set command
     *
     * @param string $command
     * @return Icebox
     */
    public function setCommand($command)
    {
        $this->command = $command;
    
        return $this;
    }

    /**
     * Get command
     *
     * @return string 
     */
    public function getCommand()
    {
        return $this->command;
    }
    
    /**
     * Принимает
     * @param int $time - время в секундах, как часто холодильник должен слать данные с датчиков
     * @return boolean 
     */
    public function setTimeOfData($time)
    {
        if(!empty($this->imei) && !empty($this->passwod)){
            $this->command = 'AUTH "'.$this->passwod.'"'."\n".'SET "gprs.1.poll.delay" = '.$time."\n".'SET "gprs.2.poll.delay" = '.$time."\n".'SAVE'."\n";
            return true;
        }
        
        return false;
    }
    
    public function srednee($ar)
    {
        
        if(count($ar)>1){
            //sort($ar);
            
            //$ar = array_slice($ar, 1, -1); 
            
            // Откидываем пока определение порога
            /*
            $old = $old1 = $res = $counter = 0;
            $good = array();
            $porog = 100;
            foreach ($ar as $v){
                if($old){
                    if(abs($v-$old) < $porog){
                        if($old1){
                            if(abs($v-$old) < $porog || abs($v-$old1) < $porog){
                                if(abs($v-$old1) < $porog){
                                    $old = $old1;
                                }
                            }
                            else {
                                return 0;
                            }
                        }
                        $counter ++;
                        $good[] = $old;
                        $old = $v;
                    }
                    else {
                        $old1 = $old1;
                        $old = $v;
                    }
                }
                else $old = $v;
            }
            var_dump($good); exit;
            $res = array_sum($good)/count($good);
             * 
             */
            $res = ceil(array_sum($ar)/count($ar));
            return $res;
        }
        return 0;
    }
    
    public function finishedCalibrate()
    {
        $c = $this->getCalibWeight();
        
        if(strpos($c,'a')===0 && strpos($c,'b') && strpos($c,'c') && strpos($c,'d')){
            if($a = explode(',', $c)){
                $w1 = $w2 = $w3 = array();
                foreach ($a as $v){
                    $z = explode(':',$v); // значения веса с 2-х датчиков
                    if(count($z)==1) {
                        $abc = $v; // буквенный номер шага
                    }
                    else {
                        if($z[0]>100 && $z[0]<10000){
                            $w = $z[0];
                        }
                        elseif ($z[1]>100 && $z[1]<10000) {
                            $w = $z[1];
                        }
                        else {
                            $w = 0;
                        }
                        switch ($abc) {
                            case 'b':
                                if($w) $w1[] = $w;
                                break;
                            case 'c':
                                if($w) $w2[] = $w;
                                break;
                            case 'd':
                                if($w) $w3[] = $w;
                                break;
                        }
                    }
                }
                $w1 = $this->srednee($w1);
                $w2 = $this->srednee($w2);
                $w3 = $this->srednee($w3);
                
                $critical = ($w2-$w1)*100/($w3-$w1);
                $calib = $w1.','.$w2.','.$w3.':0,'.$critical.',100';
                $this->setCalibWeight($calib);
            }
            
            return true;
        }
        $this->setCalibWeight('');
        return false;
    }
    
    public function getPorog()
    {
        $calib_w = explode(':', $$this->getCalibWeight());
        $calib_w = (count($calib_w)==2)?explode(',', $calib_w[1]):false;
        if(count($calib_w)==3){
            return $calib_w[1];
        }
        return false;
    }
    /**
     * @var integer
     */
    private $time_report;


    /**
     * Set time_report
     *
     * @param integer $timeReport
     * @return Icebox
     */
    public function setTimeReport($timeReport)
    {
        $this->time_report = $timeReport;
    
        return $this;
    }

    /**
     * Get time_report
     *
     * @return integer 
     */
    public function getTimeReport()
    {
        return $this->time_report;
    }
    /**
     * @var string
     */
    private $calib_shelf;


    /**
     * Set calib_shelf
     *
     * @param string $calibShelf
     * @return Icebox
     */
    public function setCalibShelf($calibShelf)
    {
        $this->calib_shelf = $calibShelf;
    
        return $this;
    }

    /**
     * Get calib_shelf
     *
     * @return string 
     */
    public function getCalibShelf()
    {
        return $this->calib_shelf;
    }
    /**
     * @var integer
     */
    private $dooropen;


    /**
     * Set dooropen
     *
     * @param integer $dooropen
     * @return Icebox
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
     * @var string
     */
    private $calib_count;


    /**
     * Set calib_count
     *
     * @param string $calibCount
     * @return Icebox
     */
    public function setCalibCount($calibCount)
    {
        $this->calib_count = $calibCount;
    
        return $this;
    }

    /**
     * Get calib_count
     *
     * @return string 
     */
    public function getCalibCount()
    {
        return $this->calib_count;
    }
}