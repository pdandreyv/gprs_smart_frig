<?php 

namespace gprs\ClientBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use gprs\AdminBundle\Entity\Icebox;
use gprs\AdminBundle\Entity\Service;
use gprs\AdminBundle\Entity\Location;
use gprs\AdminBundle\Entity\Googlecoord;
use gprs\AdminBundle\Entity\Temperature;
use gprs\AdminBundle\Entity\Weight;
use gprs\AdminBundle\Entity\Dooropen;
use gprs\AdminBundle\Entity\Data;
use gprs\AdminBundle\Entity\Alarm;
use gprs\ClientBundle\BeeSms;

use gprs\AdminBundle\Entity\Settings;

class ApiController extends Controller
{
    public function indexAction()
    {
        echo "1";
        exit;
    }
    
    public function sendSMS($number,$msg)
    {
        $ch = curl_init("http://sms.ru/sms/send");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_POSTFIELDS, array(
            "api_id"		=>	"1ecbf6e4-53a0-7464-4de2-9373048377e2",
            "to"			=>	$number,
            "text"		=>	iconv("windows-1251","utf-8",$msg)
        ));
        $body = curl_exec($ch);
        curl_close($ch);
    }
    
    public function sendBeeSMS($number,$msg,$logger)
    {
        header("Content-Type: text/xml; charset=UTF-8");
        $sender_name='1939';
        $sms= new BeeSms('188.3','UBCGroup1');
        $result=$sms->post_message($msg, $number, $sender_name);
        $logger->addInfo(print_r($result,1));
    }
    
    /**
     * @Route("/api/syn")
     * @Method({"POST"})
     * @ApiDoc(
     *  description="Синхронизация. Возвращает все обновленные записи по холодильникам",
     *  input="gprs\ClientBundle\Form\SynchronizationType"
     * )
     */
    public function synchronizationAction(Request $request)
    {
        $post = $request->request->all();
        $post = $post['syn'];
        
        if(isset($post['login']) && isset($post['password']) && isset($post['date'])){
            $em = $this->getDoctrine()->getEntityManager();
            
            // encoding password
            $user = $em->getRepository('gprsAdminBundle:User')->findOneBy(array('username'=>$post['login']));
            if($user){
                $encoder = $this->container->get('security.encoder_factory')->getEncoder($user);
                $password = $encoder->encodePassword($post['password'], $user->getSalt());
                if($password != $user->getPassword()){
                    echo json_encode(array('error_code'=>3,'error_message'=>'Password not correct'));
                    exit;
                }
                
                // Отправка обновленных данных
                if(empty($post['date'])){
                    $data = $em->createQuery('SELECT i FROM gprsAdminBundle:Icebox i')->getArrayResult();
                }
                else {
                    $date = new \DateTime($post['date']);
                    $query = $em->createQuery('SELECT i FROM gprsAdminBundle:Icebox i WHERE i.updated_at > :date')
                                ->setParameter(':date',$date);
                    $data = $query->getArrayResult();
                }
                echo json_encode($data);
                exit;
            }
            echo json_encode(array('error_code'=>2,'error_message'=>'Access denied'));
            exit;
        }
        echo json_encode(array('error_code'=>1,'error_message'=>'Not all params'));
        exit;
    }
    
    public function getLogger($name)
    {
        $name = 'api_'.$name.'_'.$this->container->getParameter('site');
        $logger = new Logger($name);
        $logger->pushHandler(new StreamHandler($this->get('kernel')->getRootDir().'/logs/'.$name.'.log', Logger::DEBUG));
        
        return $logger;
    }
    /**
     * @Route("/api/location")
     * @Method({"POST"})
     * @ApiDoc(
     *  description="Локация холодильника. Возвращает 'false' если неправильные параметры. Возвращает true, если все хорошо.",
     *  input="gprs\ClientBundle\Form\LocationType"
     * )
     */
    public function locationAction(Request $request)
    {
        $logger = $this->getLogger('location');
        
        $post = $request->request->all();
        $post = isset($post['location']) ? $post['location'] : '';
        
        $lat = false;
        $lng = false;
        if(isset($post['phone']) && isset($post['mess'])){
            $logger->addInfo("Sms was received: from {$post['phone']}, mess: ".$post['mess']);
            $em = $this->getDoctrine()->getEntityManager();
            /*
            // Работа над сообщением - вычлинение координат (временное решение)
            $ar = explode(',', $post['mess']);

            if(count($ar)==2){
                if(is_numeric(trim($ar[0])) && is_numeric(trim($ar[1]))){
                    $lat = trim($ar[0]);
                    $lng = trim($ar[1]);
                }
            }
            */
            
            // Решение для вычисления GOOGLE координат
            /*
             *  sms request: 1110000
                sms response: #25500127657aae#1#0000###
                sms response format: mcc mnc lac cid ?num ?signal
                i.e.: mcc=255, mnc=1 lac=10085 (i.e. hexdec("2765")) cid=31406 (i.e. hexdec("7aae"))
             */

            if(count(explode('#', $post['mess']))==7){
                $google = $em->getRepository('gprsAdminBundle:Googlecoord')->findOneBy(array('sms'=>$post['mess']));
                if($google){
                    $time = time() - $google->getUpdatedAt()->getTimestamp();
                    if($time < 3600*24*$this->container->getParameter('google_coord_cache_days')){
                        $lat = $google->getLat();
                        $lng = $google->getLng();
                    }
                }

                if(!$lat) {
                    $ar = $this->getCoordinates($post['mess'],$logger);
                    $lat = $ar['lat'];
                    $lng = $ar['lng'];

                    // exit script if cannot geocode cell e.g. not on google's database
                    if ($lat == 0 and $lon == 0){
                        $logger->addInfo("ERROR: cannot determine cell tower location");
                        echo json_encode(array('error_code'=>4,'error_mess'=>"Cannot determine cell tower location: lat=$lat; lng=$lng"));
                        exit;
                    }

                    $changed = false;
                    // Если координаты устарели и не менялись
                    if($google){
                        $google->setCountUpdates($google->getCountUpdates()+1);
                        if($google->getLat() == $lat && $google->getLng() == $lng){
                            $google->setStatus(2);
                            $changed = true;
                        }
                        else {
                            $google->setStatus(3);
                        }
                        $em->persist($google);
                        $em->flush();
                    }
                    // Если ранее небыло такого запроса или координаты поменялись - создаем новую запись в базу
                    if(!$changed){
                        $google = new Googlecoord();
                        $google->setSMS($post['mess'])
                               ->setLat($lat)
                               ->setLng($lng)
                               ->setStatus(1)
                               ->setCountUpdates(0);

                        $em->persist($google);
                        $em->flush();
                    }
                }
            }
            // В противном случае получаем смс о том, что холодильник пуст
            else {
                $icebox = $em->getRepository('gprsAdminBundle:Icebox')->findOneBy(array('phone'=>$post['phone']));
                if($icebox){
                    $icebox->setStatus(4);
                    $em->persist($icebox);
                    $em->flush();

                    $logger->addInfo("Weith was exepted"."\nParams: ".print_r($post,1));
                    echo "Weith was exepted";
                }
                else {
                    $logger->addInfo("No Icebox for this Phone. "."\nParams: ".print_r($post,1));
                    echo json_encode(array('error_code'=>5,'error_mess'=>'No Icebox for this Phone'));
                }
                exit;
            }
            
            
            if($lat && $lng) {
                $icebox = $em->getRepository('gprsAdminBundle:Icebox')->findOneBy(array('phone'=>$post['phone']));

                if($icebox){
                    $location = new Location();
                    $location->setIcebox($icebox)
                            ->setLat($lat)
                            ->setLng($lng);
                    $em->persist($icebox);
                    $em->persist($location);
                    $em->flush();

                    // Если координаты устройства отличваются от начальных, устанавливаем статус Alarm!
                    if($icebox->getLat() != $lat || $icebox->getLng() != $lng){
                        $icebox->setStatus(3);
                        $em->persist($icebox);
                        $em->flush();
                    }
                    $logger->addInfo("Was good send coordinates: lat=$lat; lng=$lng. "."\nParams: ".print_r($post,1));
                    echo "Was good send coordinates: lat=$lat; lng=$lng";
                }
                else {
                    $logger->addInfo("No Icebox for this Phone. "."\nParams: ".print_r($post,1));
                    echo json_encode(array('error_code'=>3,'error_mess'=>'No Icebox for this Phone'));
                }
            }
            else {
                $logger->addInfo("No correct mess. "."\nParams: ".print_r($post,1));
                echo json_encode(array('error_code'=>2,'error_mess'=>'No correct mess'));
            }
        }
        else {
            $post['IP'] = $_SERVER['REMOTE_ADDR'];
            $logger->addInfo("Error: Not all parameters. "."\nParams: ".print_r($post,1));
            echo json_encode(array('error_code'=>1,'error_mess'=>'Not all parameters'));
        }
        exit;
    }
    
    public function getCoordinates($sms, &$logger)
    {
        $error = false;
        $ar = explode('#', $sms);

        if(count($ar)==7){
            $mcc = number_format(substr($ar[1],0,3));
            $mnc = number_format(substr($ar[1],3,3));
            $lac = hexdec(substr($ar[1],6,4));
            $cid = hexdec(substr($ar[1],10,4));

            $data = 
                    "\x00\x0e". 
                    "\x00\x00\x00\x00\x00\x00\x00\x00". 
                    "\x00\x00". 
                    "\x00\x00". 
                    "\x00\x00". 
                    "\x1b". 
                    "\x00\x00\x00\x00".  
                    "\x00\x00\x00\x00".  
                    "\x00\x00\x00\x00".  
                    "\x00\x00". 
                    "\x00\x00\x00\x00".  
                    "\x00\x00\x00\x00".  
                    "\x00\x00\x00\x00".  
                    "\x00\x00\x00\x00".  
                    "\xff\xff\xff\xff". 
                    "\x00\x00\x00\x00";

            $is_umts_cell = ($cid > 65535);
            if ($is_umts_cell) // GSM: 4 hex digits, UTMS: 6 hex digits 
                $data[0x1c] = chr(5);
            else
                $data[0x1c] = chr(3);

            $hexmcc = substr("00000000".dechex($mcc),-8);
            $hexmnc = substr("00000000".dechex($mnc),-8);
            $hexlac = substr("00000000".dechex($lac),-8);
            $hexcid = substr("00000000".dechex($cid),-8);

            //echo "<p>MCC=$hexmcc MNC=$hexmnc LAC=$hexlac CID=$hexcid";

            $data[0x11] = pack("H*",substr($hexmnc,0,2));
            $data[0x12] = pack("H*",substr($hexmnc,2,2));
            $data[0x13] = pack("H*",substr($hexmnc,4,2));
            $data[0x14] = pack("H*",substr($hexmnc,6,2));

            $data[0x15] = pack("H*",substr($hexmcc,0,2));
            $data[0x16] = pack("H*",substr($hexmcc,2,2));
            $data[0x17] = pack("H*",substr($hexmcc,4,2));
            $data[0x18] = pack("H*",substr($hexmcc,6,2));

            $data[0x27] = pack("H*",substr($hexmnc,0,2));
            $data[0x28] = pack("H*",substr($hexmnc,2,2));
            $data[0x29] = pack("H*",substr($hexmnc,4,2));
            $data[0x2a] = pack("H*",substr($hexmnc,6,2));

            $data[0x2b] = pack("H*",substr($hexmcc,0,2));
            $data[0x2c] = pack("H*",substr($hexmcc,2,2));
            $data[0x2d] = pack("H*",substr($hexmcc,4,2));
            $data[0x2e] = pack("H*",substr($hexmcc,6,2));

            $data[0x1f] = pack("H*",substr($hexcid,0,2));
            $data[0x20] = pack("H*",substr($hexcid,2,2));
            $data[0x21] = pack("H*",substr($hexcid,4,2));
            $data[0x22] = pack("H*",substr($hexcid,6,2));

            $data[0x23] = pack("H*",substr($hexlac,0,2));
            $data[0x24] = pack("H*",substr($hexlac,2,2));
            $data[0x25] = pack("H*",substr($hexlac,4,2));
            $data[0x26] = pack("H*",substr($hexlac,6,2));

            // Посылаем запрос в GOOGLE
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "http://www.google.com/glm/mmap");
            curl_setopt($ch, CURLOPT_HEADER, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1); 
            curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-type: application/binary"));
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_POST, 1);
            $response = curl_exec($ch);

            if (curl_errno($ch)) {
                $logger->addInfo("CURL error: ".curl_error($ch));
                $error = true;
            }

            $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $str = substr($response, $header_size);

            curl_close($ch);

            $opcode1 = ((ord($str[0]) << 8)) | ord($str[1]);
            $opcode2 = ord($str[2]);

            if (($opcode1 != 0x0e) || ($opcode2 != 0x1b)) {
                $logger->addInfo("Error: Invalid opcode $opcode1 $opcode2. Maybe the LAC/CID is invalid");
                $error = true;
            }
            
            $retcode = ((ord($str[3]) << 24) | (ord($str[4]) << 16) | (ord($str[5]) << 8) | (ord($str[6])));
            if ($retcode != 0) {
                $logger->addInfo("Error: Invalid return code $retcode. Maybe the LAC/CID is invalid");
                $error = true;
            }
            
            if(!$error){
                $lng = ((ord($str[11]) << 24) | (ord($str[12]) << 16) | (ord($str[13]) << 8) | (ord($str[14]))) / 1000000;
                $lat = ((ord($str[7]) << 24) | (ord($str[8]) << 16) | (ord($str[9]) << 8) | (ord($str[10]))) / 1000000;
            } else {
                $lat = 0;
                $lng = 0;
            }
            $res = array('lat'=>$lat,'lng'=>$lng);
            
            return $res;
        }
    }
    
    /**
     * @Route("/api/data")
     * @Method({"POST"})
     * @ApiDoc(
     *  description="Получение всех данных от холодильника. Возвращает 'false' если неправильные параметры. Возвращает true, если все хорошо.",
     *  input="gprs\ClientBundle\Form\DataType"
     * )
     */
    public function dataAction(Request $request)
    {
//        $lat1 = 50.006218; $lng1 = 36.251453;
//        $lat2 = 50.005417; $lng2 = 36.253178;
//        $radius = sqrt(pow(111000*($lat1-$lat2),2)+pow(6371000*($lng1-$lng2)*3.14159265358979323846/180*cos($lat2*3.14159265358979323846/180),2));
//        echo $radius;
        
//        $em = $this->getDoctrine()->getEntityManager();
//        $last_data = $em->getRepository('gprsAdminBundle:Data')->findLast();
//        var_dump($last_data);
//        exit;
        
        $logger = $this->getLogger('data');
        
        $post = $request->request->all();
        $logger->addInfo(print_r($post,1));
        
        if(isset($post['iccid']) && isset($post['par'])){
            try{
                $em = $this->getDoctrine()->getEntityManager();
                $icebox = $em->getRepository('gprsAdminBundle:Icebox')->findOneBy(array('iccid'=>$post['iccid']));

                if($icebox){
                    $last_data = $em->getRepository('gprsAdminBundle:Data')->findLast($icebox->getId());
                    $data = new Data();
                    $data->setIcebox($icebox);
                    $data->setNetRssi($post['net']['rssi'])
                        ->setNetSrvCid($post['net']['srv']['cid'])
                        //->setNetCids()
                        ->setPin1Count($post['pin'][1]['tcount']['low'])
                        ->setPin1Uptime($post['pin'][1]['uptime'][1]['low'])
                        ->setAdc($post['adc']['val'])
                        ->setTemp($post['temp']['val'])
                        ->setBat($post['bat']['val'])
                        ->setPar1($post['par'][1]['val'])
                        ->setPar2($post['par'][2]['val']);
                    
                    // Определение Открытия дверей
                    if(isset($post['pin'][1]['tcount']['low'])){
                        $pin1_count = $post['pin'][1]['tcount']['low'];
                        if($last_data){
                            if($pin1_count == $last_data['pin1_count']){
                                $count_open = 0;
                            }
                            elseif($last_data['pin1_count'] > $pin1_count) {
                                $count_open = ceil($pin1_count/2);
                            }
                            else {
                                $count_open = ceil(($pin1_count-$last_data['pin1_count'])/2);
                            }
                        }
                        else {
                            $count_open = ceil($pin1_count/2);
                        }
                        $data->setDooropen($count_open);
                    }
                    // Определение веса холодильника
                    if(isset($post['par'][1]['val']) && isset($post['par'][2]['val']) && $calib_w = $icebox->getCalibWeight()){
                        $calib_w = explode(':', $calib_w);
                        $calib_w = (count($calib_w)==2)?explode(',', $calib_w[0]):false;
                        if(count($calib_w) == 3){
                            // $calib_w[0] - цыфра пустого холодильника
                            // $calib_w[1] - порог при пересечении когорого - аларм
                            // $calib_w[2] - полный холодильник
                            if($this->container->getParameter('site') == 'inbev') {
                                $w = $post['par'][2]['val'];
                            }
                            else {
                                $w = $post['par'][1]['val'];
                            }
                            // Если пришедшие параметры в пределах нормы
                            if($w >= $calib_w[0]/2 && $w <= $calib_w[2]*2) {
                                if($w<$calib_w[1]) { // аларм - нужно заполнять холодильник
                                    $val = 0; 
                                }
                                else $val = 1; // холодильник заполнен допустимо
                                $weight = ($w-$calib_w[0])*100/($calib_w[2]-$calib_w[0]);
                                $weight = ($weight >= 0 && $weight <= 200) ? $weight : 0 ;
                                $data->setWeight($weight);

                                // Если аларм - отправляем смс
                                $alarm = $em->getRepository('gprsAdminBundle:Alarm')->findOneBy(array('weight'=>1,'status'=>0,'icebox_id'=>$icebox->getId()));

                                if($val==0){
                                    $logger->addInfo('Was Alarm for weight: '.$weight);
                                    if(!$alarm) {
                                        $alarm = new Alarm();
                                        $alarm->setIcebox($icebox);
                                        $alarm->setStatus(0);
                                        $alarm->setWeight(1);
                                        $em->persist($alarm);
                                        $em->persist($icebox);
                                        $em->flush();

                                        $data->setWeightStatus(0); // OOS наступило

                                        $traders = $em->getRepository('gprsAdminBundle:Trader')->getTradersForAlarm('weight');
                                        if($this->container->getParameter('site') == 'inbev') {
                                            $msg = $this->__('sms_weight',array('%address%'=>$icebox->getAddress(),'%model%'=>$icebox->getModel()));
                                            $msg = iconv(mb_detect_encoding($msg, mb_detect_order(), true), "CP1251", $msg);
                                        }
                                        else {
                                            $msg = $this->__('sms_weight',array('%address%'=>$icebox->getAddress(),'%model%'=>$icebox->getModel()));
                                        }
                                        foreach($traders as $trader){
                                            $number = $trader['phone'];
                                            if($this->container->getParameter('site') == 'inbev') {
                                                $this->sendSMS($number,$msg);
                                                $logger->addInfo('Was send inbev sms: '.$msg.'; to number: '.$number);
                                            }
                                            else {
                                                $this->sendBeeSMS($number,$msg,$logger);
                                            }
                                        }
                                    }
                                    else {
                                        $data->setWeightStatus(2); // OOS небыл устранен с последнего наступления
                                    }
                                }
                                elseif($alarm) {
                                    $alarm->setStatus(1);
                                    $em->persist($alarm);
                                    $em->flush();
                                    $data->setWeightStatus(3); // OOS Устранен
                                }
                                else {
                                    $data->setWeightStatus(1); // Все в порядке, нет OOS
                                }
                            }
                        }
                    }
                    $site = $this->container->getParameter('site');
                    if($site != 'inbev') { // Для инбева температура определяется в другом месте
                        // Определение температуры холодильника
                        if(isset($post['adc']['val']) && isset($post['temp']['val']) && $calib_t = $icebox->getCalibTemp()){
                            // $calib_t[0] - порог минимально допустимой температуры в цельсиях (от датчика это максимальная цыфра в тугриках)
                            // $calib_t[1] - порог максимально допустимой температуры в цельсиях (от датчика это минимальная цыфра в тугриках)
                            $calib_t = explode(':', $calib_t);
                            $cd = (count($calib_t)==2)?explode(',', $calib_t[0]):false;
                            $ct = (count($calib_t)==2)?explode(',', $calib_t[1]):false;
                            if(count($cd) == 2 && count($ct) == 2){
                                $t = $post['adc']['val'];
                                if($t < $cd[1] || $t > $cd[0]) $val = 0; // критическая температура - аларм
                                else $val = 1; // температура в допустимых пределах
                                $m = abs($cd[1]-$cd[0])/abs($ct[1]-$ct[0]);
                                $tempr = round($ct[0]+(abs($t-$cd[0])/$m),1);
                                $data->setTInside($tempr);
                                $t_out = $post['temp']['val'];
                                if($site=='pepsi') $t_out -= 6;
                                $data->setTOut($t_out);
                            }
                        }
                    }
                    $save_icebox = false;
                    // Если холодильник доселе был выключен, делаем его статус - включен
                    if($icebox->getStatus()==1) {$icebox->setStatus(2); $save_icebox = true;}

                    // Если запущен режим калибровки веса - сохраняем все приходящие данные по весу
                    if (strpos($icebox->getCalibWeight(), 'a')===0) {
                        $icebox->setCalibWeight($icebox->getCalibWeight().','.$post['par'][1]['val'].':'.$post['par'][2]['val']);
                         $save_icebox = true;
                    }
                    
                    // Если изменился Imei - перезаписываем его
                    if($icebox->getImei() != $post['imei']) {$icebox->setImei($post['imei']); $save_icebox = true;}
                    
                    $em->persist($data);
                    $em->persist($icebox);
                    $em->flush();
                    // Определение координат холодильника 
                    /*if(isset($post['net'])){
                        // Выбираем из массива активные вышки
                        $abc = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P');
                        $visible = array(); // Активные вышки
                        $visible_cids = array(); // Сиды активных вышек
                        $three_max_rx = array(0,0,0); // массив 3-х максимальных сигналов
                        $three_max_cid = array(); // Сиды максимальных сигналов
                        foreach ($abc as $a){
                            $rx = $post['net'][$a]['rxlev'];
                            if($rx > 0){
                                $cid =$post['net'][$a]['cid'];
                                $visible[$cid]=$post['net'][$a];
                                $visible_cids[]=$cid;
                                foreach ($three_max_rx as $k => $max){
                                    if($max < $rx){
                                        $three_max_rx[$k] = $rx;
                                        $three_max_cid[$k] = $visible[$cid];
                                        break;
                                    }
                                }
                            }
                        }
                        // Если есть новые вышки - записываем их в базу
                        $towers = $em->getRepository('gprsAdminBundle:Tower')->findByCids($visible_cids);
                        if(count($towers) < count($visible_cids) && count($visible_cids) > 0){
                            if(count($towers) > 0){
                                foreach($towers as $t){
                                    unset($visible_cids[$t['cid']]);
                                }
                            }
                            foreach($visible_cids as $t){
                                $tower = new Tower($t);
                                $em->persist($tower);
                            }
                            $em->flush();
                            $em->clear();
                        }
                    }
                    if(count($three_max_cid)>2){
                        $loc = $this->getMiddleCoordinates($three_max_cid);
                        // если определены координаты - записываем их в базу
                        if($loc['lat'] && $loc['lng']){
                            $location = new Location();
                            $location->setIcebox($icebox)->setLat($lat)->setLng($lng);
                            $em->persist($location);
                            $em->flush();
                        }
                    }
                    // Определение веса холодильника
                    if(isset($post['par'][1]['val']) && isset($post['par'][2]['val'])){
                        $val = $post['par'][1]['val'].'.'.$post['par'][2]['val'];
                        $weight = new Weight();
                        $weight->setIcebox($icebox);
                        $weight->setWeight($val);
                        $em->persist($weight);
                        $em->persist($icebox);
                        $em->flush();
                    }
                    else {
                        $logger->addInfo('Not isset post[par]');
                    }
                    // Определение температуры холодильника
                    if(isset($post['adc']['val']) && isset($post['temp']['val'])){
                        $temperature = new Temperature();
                        $temperature->setIcebox($icebox);
                        $temperature->setTInside($post['adc']['val']);
                        $temperature->setTOut($post['temp']['val']);
                        $em->persist($temperature);
                        $em->persist($icebox);
                        $em->flush();
                    }
                    else {
                        $logger->addInfo('Not isset post[adc] || post[temp]');
                    }
                    // Определение Открытия дверей
                    if(isset($post['pin'][1]['tcount']['low'])){
                        $dooropen = new Dooropen();
                        $dooropen->setIcebox($icebox);
                        $dooropen->setCount(($post['pin'][1]['tcount']['low']-1)/2);
                        $em->persist($dooropen);
                        $em->persist($icebox);
                        $em->flush();
                    }
                    else {
                        $logger->addInfo('Not isset post[pin][1][tcount][low]');
                    }
                     * 
                     */
                    
                    // Вставка кода только для инбев по просьбе Макса 06.07.15
                    if($site == 'inbev' && isset($post['net']['srv']['lat'])) {
                        $icebox->setLat($post['net']['srv']['lat'])
                            ->setLng($post['net']['srv']['lon']);
                        $save_icebox = true;
                        $logger->addInfo('post[net][srv][lat]'.print_r($post['net']['srv']['lat'],1));
                    }
                    // Конец вставки
                    
                    if($com = $icebox->getCommand()){
                        $icebox->setCommand('');
                        $save_icebox = true;
                        echo $com;
                    }
                    if($save_icebox){
                        $em->persist($icebox);
                        $em->flush();
                    }
                }
                else {
                    Icebox::saveIccid($post['iccid']);
                    $logger->addInfo('iccid ('.$post['iccid'].') not include not one icebox');
                }
            }
            catch (Exception $e){
              $logger->addInfo('Catch error: '.$e->getMessage());
            }
        }
        else {
            $logger->addInfo('Not isset post[iccid]');
        }
        exit;
    }
    
    /**
     * @Route("/api/data2")
     * @Method({"POST"})
     * @ApiDoc(
     *  description="Получение всех данных от Демидова по полкам.",
     *  input="gprs\ClientBundle\Form\DataType"
     * )
     */
    public function data2Action(Request $request)
    {
        // Данные приходят часто раз в 5-10 сек, поэтому случайным образом ввожу пропуск и обработку данных одну из 10-50 путем ремдомных чисел
        $rand = rand(0, 10);
        if($rand == 1) { // 1 - это чило будет выпадать не чаще 1 раз из 3, а может и реже
            $logger = $this->getLogger('polki');

            $post = $request->request->all();
            $logger->addInfo(print_r($post,1));

            $em = $this->getDoctrine()->getEntityManager();
//            $icebox_id = 1; // Немного хардкода, пока у нас один холодильник и эта фукнция только для него одного
//            $data = $em->getRepository('gprsAdminBundle:Data')->findLastData($icebox_id,1);
//            $data = $data[0];
	    $data = new Data();
            $icebox = $em->getRepository('gprsAdminBundle:Icebox')->findOneBy(array('id'=>1));

            $data->setIcebox($icebox);
	    $data->getIceboxId(1);
            
            $t = explode('|', $post['temp']);
            $data->setTInside(round($t[0],1));
            $data->setTOut(round($t[1],1));
            $p = explode('|', $post['raw']);

            // Приходится немного хардкодить, в общем цыфри взяты из максимумов и минимумов
            
/* Инбевовский холодильник
            $p[0] = ($p[0]-64236)*100/(59228-64236);
            $p[1] = ($p[1]-65437)*100/(61102-65437);
            $p[2] = 0; // не работает
            $p[3] = ($p[3]-64612)*100/(60527-64612);
 
// molsoncoors
 *          $p[0] = ($p[0]-65399)*100/(63644-65399);
            $p[1] = ($p[1]-65406)*100/(63259-65406);
            $p[2] = ($p[2]-65123)*100/(63795-65123);
            $p[3] = ($p[3]-65303)*100/(64438-65303);
 * 
 * 65463|64778|63349|62173|0|0
 * 
 */
            $site = $this->container->getParameter('site');
            $calibrate = explode(';',$this->container->getParameter('calibrate'));
            $min = explode('|',$calibrate[0]);
            $max = explode('|',$calibrate[1]);
            for($i=0;$i<5;$i++){
                if($min[$i] && $max[$i] && $p[$i]){
                    $p[$i] = ceil(($p[$i]-$min[$i])*100/($max[$i]-$min[$i]));
                    if($p[$i]<0) $p[$i] = 0;
                    $p[$i] = $p[$i]-$p[$i]%5;

                }
            }
            if($site == 'molsoncoors' || $site == 'inbev'){
                    $a = $p[0];
                    $p[0] = $p[3];
                    $p[3] = $a;
                    $p[2] = 0;
                    $p[1] = 0;

                }
            for($i=0;$i<4;$i++){
                
                $field = 'setP'.($i+1);
                $data->$field($p[$i]);
            }
            $data->setWeight(($p[0]+$p[1]+$p[2]+$p[3])/4);
            //$data->setDooropen(32);
            
            $em->persist($icebox);
            $em->persist($data);
            $em->flush();

            $subj = "Alarm!";
            // Посылка алармов по 4 полкам
            for($i=0; $i<4; $i++){
                $alarm = $em->getRepository('gprsAdminBundle:Alarm')->findOneBy(array('p'.($i+1)=>1,'status'=>0,'icebox_id'=>$icebox->getId()));
                if($p[$i] < 10){
                    if(!$alarm) {
                        $mess = "Alarm! Shelf ".($i+1)." < 10% of weight. At ".$icebox->getAddress();
                        $this->setMessAlarm('p'.($i+1),$icebox,$mess,$subj);
                    }
                }
                elseif($alarm) {
                    $alarm->setStatus(1);
                    $em->persist($alarm);
                    $em->flush();
                    $subj = "Fixed alarm";
                    $mess = "Shelf ".($i+1)." is good. At ".$icebox->getAddress();
                    $this->setMessAlarm('p'.($i+1),$icebox,$mess,$subj,false);
                }
            }
            // Посылка алармов по температуре
            $alarm = $em->getRepository('gprsAdminBundle:Alarm')->findOneBy(array('temperatura'=>1,'status'=>0,'icebox_id'=>$icebox->getId()));
            if($t[0] > 7){
                if(!$alarm) {
                    $mess = "Alarm! Temperature inside > 7 grade. At ".$icebox->getAddress();
                    $this->setMessAlarm('temperatura',$icebox,$mess,$subj);
                }
            }
            elseif($alarm){
                $alarm->setStatus(1);
                $em->persist($alarm);
                $em->flush();
                $subj = "Fixed alarm";
                $mess = "Temperature inside is good. At ".$icebox->getAddress();
                $this->setMessAlarm('temperatura',$icebox,$mess,$subj,false);
            }
            
            // Если холодильник доселе был выключен, делаем его статус - включен
            if($icebox->getStatus()==1) {
                $icebox->setStatus(2);
                $em->persist($icebox);
                $em->flush();
                $alarm = $em->getRepository('gprsAdminBundle:Alarm')->findOneBy(array('power'=>1,'status'=>0,'icebox_id'=>$icebox->getId()));
                $subj = "Power on";
                if($alarm){
                    $alarm->setStatus(1);
                    $em->persist($alarm);
                    $em->flush();
                    $subj = "Fixed alarm";
                }
                $mess = "Power on. At ".$icebox->getAddress();
                $this->setMessAlarm('power',$icebox,$mess,$subj,false);
            }
        }
        exit;
    }
    
    public function okruglenie($weight)
    {
        //$res = round($weight*2, -1)/2;
        $res = $weight;
        /*if($weight>50){
            
        }else{
            
        }*/
        return $res;
    }
    
    /**
     * @Route("/api/data2")
     * @Method({"POST"})
     * @ApiDoc(
     *  description="Получение всех данных от Демидова по полкам.",
     *  input="gprs\ClientBundle\Form\DataType"
     * )
     */
    public function data3Action(Request $request)
    {
        $em = $this->getDoctrine()->getEntityManager();

        $post = $request->request->all();

        $logger = $this->getLogger('baltika3');
        $logger->addInfo(print_r($post, 1));

        $icebox = $em->getRepository('gprsAdminBundle:Icebox')
            ->findOneBy(array(
                'iccid'=>$post['Login'],
                'passwod'=>$post['Passwd']
            ));

        if($icebox){
            $data = new Data();
            $data->setIcebox($icebox);
            
            // Определение веса холодильника
            if(isset($post['Weight']['val']) && $calib_w = $icebox->getCalibWeight()){
                $w=$post['Weight']['val'];
                $calib_w = explode(':', $calib_w);
                $calib_w = (count($calib_w)==2)?explode(',', $calib_w[0]):false;
                if(count($calib_w) == 3){
                    // $calib_w[0] - цыфра пустого холодильника
                    // $calib_w[1] - порог при пересечении когорого - аларм
                    // $calib_w[2] - полный холодильник
                    
                    // Если пришедшие параметры в пределах нормы
                    if($w >= $calib_w[0]/2 && $w <= $calib_w[2]*2) {
                        if($w<$calib_w[1]) { // аларм - нужно заполнять холодильник
                            $val = 0; 
                        }
                        else $val = 1; // холодильник заполнен допустимо
                        $weight = ($w-$calib_w[0])*100/($calib_w[2]-$calib_w[0]);
                        $weight = ($weight >= 0 && $weight <= 200) ? $weight : 0 ;
                        $data->setWeight($weight);
                    }
                }
            }
            
            // Открытие дверей
            $dooropen = intval($icebox->getDooropen());
            if(isset($post['Door']['count']) && $post['Door']['count']<15){
                $dooropen = floor($post['Door']['count']/2);
                $icebox->setDooropen($dooropen);
            }

            $data->setDooropen($dooropen); 
            $data->setPar1($post['Weight']['max']);
            $data->setTInside(ceil($post['iTemp']['val']));
            $data->setTOut(ceil($post['eTemp']['val']));
            $data->setNetSrvCid($post['lbs']['cid']);
            
            // Определение веса полочек
            if(isset($post['Shelf'])){
                $data->setP1d($post['Shelf'][1]['val']);
                $data->setP2d($post['Shelf'][2]['val']);
                $data->setP3d($post['Shelf'][3]['val']);
                $data->setP4d($post['Shelf'][4]['val']);
                if(isset($post['Shelf'][5]['val'])) $data->setP5d($post['Shelf'][5]['val']);
                if(isset($post['Shelf'][6]['val'])) $data->setP6d($post['Shelf'][6]['val']);
                
                // Определения веса полочек, если они откалиброваны
                if($calib = $icebox->getCalibShelf()){
                    $all_weight = 0;
                    $calib = explode(':', $calib);
                    if(count($calib)==2){
                        $cmin = explode(',', $calib[0]);
                        $cmax = explode(',', $calib[1]);
                        if(count($cmin) && count($cmax)){
                            if($calib_count = $icebox->getCalibCount()){
                                $calib_count = explode(',', $calib_count);
                            }
                            for($i=0;$i<6;$i++){
                                if($cmin[$i] && $cmax[$i] && isset($post['Shelf'][$i+1])) {
                                    $weight = ($post['Shelf'][$i+1]['val']-$cmin[$i])*100/($cmax[$i]-$cmin[$i]);
                                    $weight = ($weight >= 0 && $weight <= 200) ? $weight : '-' ;
                                    if($weight!='-') $weight = $this->okruglenie($weight);
                                    $name = 'setP'.($i+1);
                                    $data->$name($weight);
                                    $all_weight += $weight;
                                    // Определение количества бутылок, если их максимум определен
                                    if(isset($calib_count[$i]) && $calib_count[$i]>0) {
                                        $count_bottles = floor(($post['Shelf'][$i+1]['max']-$cmin[$i])/(($cmax[$i]-$cmin[$i])/$calib_count[$i]));
                                        $count_bottles = ($count_bottles >= 0 && $count_bottles <= $calib_count[$i]) ? $count_bottles : (($count_bottles < 0)?0:$calib_count[$i]) ;
                                        $name = 'setP'.($i+1).'c';
                                        $data->$name($count_bottles);
                                    }
                                }
                            }
                        }
                    }
                    // небольшой хардкод по определению веса всего холодильника по полочкам
                    $data->setWeight(floor($all_weight/5));
                }
            }

            if((int)$data->getDooropen() === 0) {
                $lastData = $em->getRepository('gprsAdminBundle:Data')
                    ->getLastItemByIceboId($icebox->getId());

                for($i = 0; $i < 5; $i++) {
                    $index = $i+1;
                    // get keys names
                    $gp = 'p'.$index;
                    $gpc = 'p'.$index.'c';
                    // set method names
                    $sp = 'setP'.$index;
                    $spc = 'setP'.$index.'c';

                    $data->$sp($lastData[$gp]);
                    $data->$spc($lastData[$gpc]);
                }
            }
            
            // Определение локации
           /* $last_location = $em->getRepository('gprsAdminBundle:Location')->getLastLocation($icebox->getId());
            if(($last_location && ($last_location['lat'] != $post['pos']['lat'] || $last_location['lng'] != $post['pos']['lon'])) || !$last_location){
                $location = new Location();
                $location->setIcebox($icebox);
                $location->setLat($post['pos']['lat']);
                $location->setLng($post['pos']['lon']);
                $location->setRadius($post['pos']['rad']);
                $em->persist($icebox);
                $em->persist($location);
                $em->flush();
            }
            */
            $icebox->setStatus(2);
           // $icebox->setLat($post['pos']['lat']);
           // $icebox->setLng($post['pos']['lon']);
            $em->persist($icebox);
            $em->persist($data);
            $em->flush();

            $save_icebox = false;
            // Если холодильник доселе был выключен, делаем его статус - включен
            if($icebox->getStatus()==1) {$icebox->setStatus(2); $save_icebox = true;}

            // Если запущен режим калибровки веса - сохраняем все приходящие данные по весу
            if (strpos($icebox->getCalibWeight(), 'a')===0) {
                $icebox->setCalibWeight($icebox->getCalibWeight().','.$post['Weight']['val'].':0');
                 $save_icebox = true;
            }
            // Если есть комманда для выполнения, она отображается и стирается
            if($com = $icebox->getCommand()){
                $icebox->setCommand('');
                $save_icebox = true;
                echo $com;
            }
            if($save_icebox){
                $em->persist($icebox);
                $em->flush();
            }

            // check icebox
            $this->checkIcebox($icebox, $data);
        }
        else {
            $logger->addInfo("Not icebox for iccid={$post['Login']} and passwod=>{$post['Passwd']}");
            echo "no icebox";
        }

        exit;
    }

    //==============================================================================
    // method checkIcebox
    // Проверяет состояние холодильного шкафа
    // @param     object     $icebox  - холодильный шкаф
    // @param     array      $data    - отформатированные данные
    // @return    void
    //==============================================================================
    public function checkIcebox($icebox, $data) {
        $em = $this->getDoctrine()->getEntityManager();
        $settings = $em->getRepository('gprsAdminBundle:Settings')
            ->find(1);

        // $opts = $this->container->getParameter('check_icebox_service');
        $opts = array(
            'check_icebox' => $settings->getCheckIcebox(),
            'shelf_limit' => $settings->getShelfLimit(),
            'icebox_limit' => $settings->getIceboxLimit(),
            'max_out_temperature' => $settings->getMaxOutTemperature(),
            'max_in_temperature' =>  $settings->getMaxInTemperature(),
        );

        // Checked?
        if(!$opts['check_icebox']) return;

        $alarmRepository = $em->getRepository('gprsAdminBundle:Alarm');
        $messages = array();

        // inner temperature
        $alarm = $alarmRepository->findOneBy(array(
            'temperatura' => 1,
            'status' => 0,
            'icebox_id' => $icebox->getId()
        ));
        $currentInTemperature = $data->getTInside();
        if($currentInTemperature > $opts['max_in_temperature']) {
            if(!$alarm) {
                $messages[] = array(
                    'param' => 'temperatura',
                    'icebox' => $icebox,
                    'html' => $this->renderView(
                        'gprsClientBundle:Email:innertemp.html.twig',
                        array('type' => 'err')
                    ),
                    'subj' => "High inner temperature",
                );
            }
        } elseif($alarm) {
            $alarm->setStatus(1);
            $em->persist($alarm);
            $em->flush();

            $messages[] = array(
                'param' => 'temperatura',
                'icebox' => $icebox,
                'html' => $this->renderView(
                    'gprsClientBundle:Email:innertemp.html.twig',
                    array('type' => 'fix')
                ),
                'subj' => "Fixed high inner temperature",
                'at' => false,
            );
        }

        // outer temperature
        $alarm = $alarmRepository->findOneBy(array(
            'temperatura' => 2,
            'status' => 0,
            'icebox_id' => $icebox->getId()
        ));
        $currentOutTemperature = $data->getTOut();
        if($currentOutTemperature > $opts['max_out_temperature']) {
            if(!$alarm) {
                $messages[] = array(
                    'param' => 'temperatura',
                    'icebox' => $icebox,
                    'html' => $this->renderView(
                        'gprsClientBundle:Email:outertemp.html.twig',
                        array('type' => 'err')
                    ),
                    'subj' => "High outer temperature",
                    'at' => true,
                    'value' => 2,
                );
            }
        } elseif($alarm) {
            $alarm->setStatus(1);
            $em->persist($alarm);
            $em->flush();

            $messages[] = array(
                'param' => 'temperatura',
                'icebox' => $icebox,
                'html' => $this->renderView(
                    'gprsClientBundle:Email:outertemp.html.twig',
                    array('type' => 'fix')
                ),
                'subj' => "Fixed high outer temperature",
                'at' => true,
                'value' => 2,
            );
        }

        // check weight
        $alarm = $alarmRepository->findOneBy(array(
            'weight' => 1,
            'status' => 0,
            'icebox_id' => $icebox->getId()
        ));

        // get all weight
        $allWeight = $data->getWeight();

        // check shelf weight
        $checkShelfWeight = FALSE;
        for($i = 0; $i < 6; $i++) {
            $index = $i+1;
            $pCol = 'p'.$index;
            $method = 'getP'.$index;

            $weight = $data->$method();
            $weight = $weight == '-' ? 0 : $weight;

            if($weight < $opts['shelf_limit']) {
                $checkShelfWeight = TRUE;

                break;
            }
        }

        if($allWeight < $opts['icebox_limit']) {
            $data->setWeightStatus(1);
            $em->persist($data);
            $em->flush();
        }

        if($checkShelfWeight || ($allWeight < $opts['icebox_limit'])) {
            if(!$alarm) {
                $messages[] = array(
                    'param' => 'weight',
                    'icebox' => $icebox,
                    'html' => $this->renderView(
                        'gprsClientBundle:Email:limit.html.twig',
                        array('type' => 'err')
                    ),
                    'subj' => "Low quantity",
                );
            }
        } elseif($alarm) {
            $alarm->setStatus(1);
            $em->persist($alarm);
            $em->flush();

            $messages[] = array(
                'param' => 'weight',
                'icebox' => $icebox,
                'html' => $this->renderView(
                    'gprsClientBundle:Email:limit.html.twig',
                    array('type' => 'fix')
                ),
                'subj' => "Fixed low quantity",
                'at' => false,
            );
        }

        // send messages
        for($i = 0; $i < count($messages); $i++) {
            $msg = $messages[$i];
            $p = $msg['param'];
            $ib = $msg['icebox'];
            $m = $msg['html'];
            $s = $msg['subj'];
            $a = isset($msg['at']) ? $msg['at'] : true;
            $v = isset($msg['value']) ? $msg['value'] : 1;

            $emails = $settings->getEmails();
            $emails = $emails ? explode(',', $emails) : $emails;

            if(is_array($emails)) {
                foreach($emails as $email) {
                    $this->sendEMail($m, $s, $email, $this->container->getParameter('email_from_alarm'), FALSE, TRUE);
                }
            }

            $this->setAlarm($p,$ib,$m,$s,$a,$v);
        }
    }

    //==============================================================================
    // method setAlarm
    // Устанавливает статус сообщения 0, и(или) отправляет сообщение
    // @param     string      $param         - параметр
    // @param     object      $icebox        - текущий холодильник
    // @param     string      $message       - сообщение для почты
    // @param     string      $subject       - тема сообщения
    // @param     boolean     $at            - фиксить alarm?
    // @param     boolean     $value         - значение для alarm
    // @return    boolean
    //==============================================================================
    public function setAlarm($param, $icebox, $message, $subject, $al = TRUE, $value = 1) {
        $em = $this->getDoctrine()->getEntityManager();

        if($al) {
            $alarm = new Alarm();
            $alarm->setIcebox($icebox);
            $alarm->setStatus(0);
            $method = 'set'.$icebox->getMethodName($param);
            $alarm->$method($value);
            $em->persist($alarm);
            $em->persist($icebox);
            $em->flush();
        }

        return $this->sendEmail($message, $subject, $icebox->getTrader()->getEmail(), $this->container->getParameter('email_from_alarm'), FALSE, TRUE);
    }
    
    public function setMessAlarm($param,$icebox,$mess,$subj,$al=true,$value=1)
    {
        $em = $this->getDoctrine()->getEntityManager();
        if($al){
            $alarm = new Alarm();
            $alarm->setIcebox($icebox);
            $alarm->setStatus(0);
            $method = 'set'.$icebox->getMethodName($param);
            $alarm->$method($value);
            $em->persist($alarm);
            $em->persist($icebox);
            $em->flush();
        }
        $traders = $em->getRepository('gprsAdminBundle:Trader')->getTradersForAlarm($param);
        foreach($traders as $trader){
            $to = $trader['email'];
            $this->sendEmail($mess,$subj,$to,$from='ubc@gmail.com');
        }
    }

    public function sendEmail($mess,$subj,$to,$from='molsoncoors@uaic.net',$file=false,$fb=false)
    {
        $mailer = $this->get('mailer'); 
        if($fb){
            $mailer = $this->get('swiftmailer.mailer.smtp_mail');
        }
        $message = \Swift_Message::newInstance() 
                ->setSubject($subj) 
                ->setFrom($from)
                ->setTo($to) 
                ->setBody($mess, 'text/html');
        if($file){
            $message->attach(\Swift_Attachment::fromPath($file));
        }
        
        $mailer->send($message);
    }
    
    public function getMiddleCoordinates($t)
    {
        $lng = 0;
        $lat = 0;
        if(count($t)==3){
            $lng = $this->getLatLng($t[0]['lng'], ($t[1]['lng'] + $t[2]['lng'])/2);
            $lat = $this->getLatLng($t[0]['lat'], ($t[1]['lat'] + $t[2]['lat'])/2);
        }
        elseif(count($t)==2){
            $lng = ($t[0]['lng'] + $t[1]['lng'])/2;
            $lat = ($t[0]['lat'] + $t[1]['lat'])/2;
        }
        return array('lat'=>$lat,'lng'=>$lng);
    }
    
    public function getLatLng($t1,$t2)
    {
        $l = 0;
        if($t1 < $t2)
            $l = $t2 - ($t2 - $t1)/3;
        else 
            $l = $t2 + ($t1 - $t2)/3;
        return $l;
    }
    
    /**
     * @Route("/api/testdata")
     * @Method({"POST"})
     * @ApiDoc(
     *  description="Data",
     *  input="gprs\ClientBundle\Form\DataType"
     * )
     */
    public function testDataAction(Request $request)
    {
        $data = $request->request->all();
        $data = $data['location']['phone'];
        
        if($data==3){
            $post = array
(
  "Proto"=>"Baltika",
  "Build"=>"54",
  "Reason"=>"ok",
  "Login"=>"BCM2708-000e-0000000008fd89a2",
  "Passwd"=>"CRPgM750YKJJ8ZdWiMRnpd5f",
  "Free"=>"944",
  "Uptime"=>"21686",
  "lbs"=>array(
    "mcc"=> "255",
    "mnc"=> "1",
    "lac"=>"10085",
    "cid"=> "31406",
  ),
  "pos"=>array(
    "lat"=>"50.0055",
    "lon"=>"36.2535",
    "rad"=>"822",
  ),
  "Interval"=>"63",
  "ModemRx"=>"757",
  "ModemTx"=>"1535",
  "iTemp"=>array(
    "val"=>"28.875",
    "min"=>"28.875",
    "max"=>"28.875",
    "err"=>"0",
  ),
  "eTemp"=>array(
    "val"=>"26.75",
    "min"=>"26.75",
    "max"=>"26.875",
    "err"=>"0",
  ),
  "Weight"=>array(
    "val"=>"2051",
    "min"=>"1727",
    "max"=>"1751",
    "err"=>"0",
  ),
  "dTemp"=>array(
    "val"=>"5.9",
    "min"=>"5.8",
    "max"=>"6.8",
    "err"=>"0",
  ),
  "Door"=>array(
    "st"=>"0",
    "err"=>"0",
    "count"=>"0",
  ),
  "Motor"=>array(
    "st"=>"0",
    "err"=>"0",
  ),
  "mCurrent"=>array(
    "val"=>"15496",
    "min"=>"15470",
    "max"=>"15522",
    "err"=> "0",
  ),
  "Shelf"=>array(
      "1"=>array(
          "val"=>"3568",
          "min"=>"32768",
          "max"=>"32768",
          "err"=> "60",
      ),
      "2"=>array(
          "val"=>"3468",
          "min"=>"32768",
          "max"=>"32768",
          "err"=> "60",
      ),
      "3"=>array(
          "val"=>"3068",
          "min"=>"32768",
          "max"=>"32768",
          "err"=> "60",
      ),
      "4"=>array(
          "val"=>"3758",
          "min"=>"32768",
          "max"=>"32768",
          "err"=> "60",
      ),
  ),
  "ModemInfo"=>""
);
        }
        else {
        $post = array
(
'password' => 'test',
    'imei' => 01322700303323,
    'ver' => 'sim900-1.0a',
    'status' => 'boot',
    'firmware' => '1137B02SIM900M64_ST_DTMF_JD_EAT',
    'datetime' => '2000/1/1 3:37:27',
    'uptime' => array
        (
            'low' => 352078,
            'high' => 0
        ),

    'iccid' => '8970199120530562118F',
    'net' => array
        (
            'rssi' => 12,
            'ber' => 0,
        ),

    'pin' => array
        (
            '1' => array
                (
                    'state' => 1,
                    'tcount' => array
                        (
                            'low' => 1,
                            'high' => 0
                        ),

                    'uptime' => array
                        (
                            '0' => array
                                (
                                    'low' => 1,
                                    'high' => 0
                                ),

                            '1' => array
                                (
                                    'low' => 352077,
                                    'high' => 0
                                )

                        )

                ),

            '2' => array
                (
                    'state' => 1,
                    'tcount' => array
                        (
                            'low' => 1,
                            'high' => 0
                        ),

                    'uptime' => array
                        (
                            '0' => array
                                (
                                    'low' => 1,
                                    'high' => 0
                                ),

                            '1' => array
                                (
                                    'low' => 352077,
                                    'high' => 0
                                )

                        )

                )

        ),

    'pot' => array
        (
            '1' => array
                (
                    'wcount' => array
                        (
                            'low' => 0,
                            'high' => 0
                        ),

                    'uptime' => array
                        (
                            '0' => array
                                (
                                    'low' => 352078,
                                    'high' => 0
                                ),

                            '1' => array
                                (
                                    'low' => 0,
                                    'high' => 0
                                )

                        )

                ),

            '2' => array
                (
                    'wcount' => array
                        (
                            'low' => 0,
                            'high' => 0
                        ),

                    'uptime' => array
                        (
                            '0' => array
                                (
                                    'low' => 352078,
                                    'high' => 0
                                ),

                            '1' => array
                                (
                                    'low' => 0,
                                    'high' => 0
                                )

                        )

                )

        ),

    'adc' => array
        (
            'val' => 8
        ),

    'temp' => array
        (
            'val' => 34
        ),

    'par' => array
        (
            '1' => array
                (
                    'val' => 1283
                ),

            '2' => array
                (
                    'val' => -1
                )

        )

);
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->generateUrl('api_data'.$data,array(),true));
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
        curl_setopt($ch, CURLOPT_POST, 1);
        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            var_dump("CURL error: ".curl_error($ch)); exit;
        }
        curl_close($ch);
        var_dump($response); exit;
    }
    
    public function __($str, $ar=array())
    {
        return $this->get('translator')->trans($str, $ar);
    }
    
    // Для проекта канады
    
    // Авторизация участника
    public function authorizationAction($post,&$logger)
    {
        if(isset($post['login']) && isset($post['password'])){
            $em = $this->getDoctrine()->getEntityManager();
            
            // encoding password
            $user = $em->getRepository('gprsAdminBundle:User')->findOneBy(array('username'=>$post['login']));
            if($user){
                $encoder = $this->container->get('security.encoder_factory')->getEncoder($user);
                $password = $encoder->encodePassword($post['password'], $user->getSalt());
                if($password != $user->getPassword()){
                    $logger->addInfo("Authorization Error: Password not correct");
                    echo json_encode(array('error_code'=>3,'error_message'=>'Password not correct'));
                    exit;
                }
                
                return $user;
            }
            $logger->addInfo("Authorization Error: Access denied");
            echo json_encode(array('error_code'=>2,'error_message'=>'Access denied'));
            exit;
        }
        $logger->addInfo("Authorization Error: Not all params");
        echo json_encode(array('error_code'=>1,'error_message'=>'Not all params'));
        exit;
    }
    
    /**
     * @Route("/api/login")
     * @Method({"POST"})
     * @ApiDoc(
     *  description="Login",
     *  input="gprs\ClientBundle\Form\LoginType"
     * )
     */
    public function LoginAction(Request $request)
    {
        $logger = $this->getLogger('login');
        $post = $request->request->all();
        $post = $post['login'];

        // Авторизация
        $user = $this->authorizationAction($post,$logger);
        
        if($user){
            echo $user->getFirstname()." ".$user->getLastName();
            exit;
        }
    }
    
    /**
     * @Route("/api/qrcode")
     * @Method({"POST"})
     * @ApiDoc(
     *  description="qrcode",
     *  input="gprs\ClientBundle\Form\QrcodeType"
     * )
     */
    public function QrcodeAction(Request $request)
    {
        $logger = $this->getLogger('qrcode');
        $post = $request->request->all();
        $post = $post['qrcode'];

        // Авторизация
        $user = $this->authorizationAction($post,$logger);
        
        if($user && isset($post['qrcode'])){
            $em = $this->getDoctrine()->getEntityManager();
            $pos = strpos($post['qrcode'], 'Serial');
            if($pos !== false){
                $code = substr($post['qrcode'],($pos+12),28);
                $logger->addInfo("Code: ".$code."\n");
                if($code){
                    $icebox = $em->getRepository('gprsAdminBundle:Icebox')->findOneBy(array('serial_number'=>$code));
                }
            }
            else {
                $icebox_id = base64_decode($post['qrcode']);
                if(is_numeric($icebox_id)) 
                    $icebox = $em->getRepository('gprsAdminBundle:Icebox')->findOneBy(array('id'=>$icebox_id));
            }
            if($icebox){
                $icebox = $icebox->toArray();
                $ls = $em->getRepository('gprsAdminBundle:Service')->findLastService($icebox['id']);
                if($ls){
                    $ls = $ls->toArray();
                    $icebox['last_service'] = $ls['created_at'];
                }/*
                $outlet = $em->getRepository('gprsAdminBundle:Outlet')->find($icebox['outlet_id']);
                if($outlet) {
                    $icebox['outlet'] = $outlet->toArray();
                }
                $icebox['address'] = $icebox['outlet']['address'];
                $icebox['lat'] = $icebox['outlet']['lat'];
                $icebox['lng'] = $icebox['outlet']['lng'];*/
                echo json_encode($icebox);
                exit;
            }
            else {
                $logger->addInfo("Qrcode Error: Not correct Qrcode"."\nParams: ".print_r($post,1));
                echo json_encode(array('error_code'=>4,'error_message'=>'Not correct Qrcode'));
                exit;
            }
        }
        $logger->addInfo("Qrcode Error: Not qrcode param");
        echo json_encode(array('error_code'=>1,'error_message'=>'Not all params'));
        exit;
    }
    
    /**
     * @Route("/api/edit_icebox")
     * @Method({"POST"})
     * @ApiDoc(
     *  description="edit_icebox",
     *  input="gprs\ClientBundle\Form\EditIceboxType"
     * )
     */
    public function editIceboxAction(Request $request)
    {
        $logger = $this->getLogger('edit_icebox');
        $post = $request->request->all();
        $post = $post['edit_icebox'];

        // Авторизация
        $user = $this->authorizationAction($post,$logger);
        
        $fields_for_edit = array(
            'serial_number',
            'title',
            'type',
            'model',
            'address',
            'contragent',
            'lat',
            'lng',
            'status'
        );
        
        if($user && isset($post['id'])){
            $em = $this->getDoctrine()->getEntityManager();
            $icebox_id = $post['id'];
            $icebox = $em->getRepository('gprsAdminBundle:Icebox')->findOneBy(array('id'=>$icebox_id));
            if($icebox){
                foreach($fields_for_edit as $f){
                    if(isset($post[$f])){
                        $method = 'set'.$icebox->getMethodName($f);
                        $icebox->$method($post[$f]);
                    }
                    $em->persist($icebox);
                    $em->flush();
                }
                echo 'true';
                exit;
            }
            else {
                $logger->addInfo("Edit Icebox Error: Not correct Icebox Id");
                echo json_encode(array('error_code'=>5,'error_message'=>'Not correct Icebox Id'));
                exit;
            }
        }
        $logger->addInfo("Edit Icebox Error: Not Id param");
        echo json_encode(array('error_code'=>1,'error_message'=>'Not Id param'));
        exit;
    }
    
        
    /**
     * @Route("/api/services_names")
     * @Method({"POST"})
     * @ApiDoc(
     *  description="Get names of services",
     *  input="gprs\ClientBundle\Form\LoginType"
     * )
     */
    public function GetNamesOfServicesAction(Request $request)
    {
        $logger = $this->getLogger('services_names');
        $post = $request->request->all();
        $post = $post['login'];

        // Авторизация
        $user = $this->authorizationAction($post,$logger);
        
        if($user){
            $em = $this->getDoctrine()->getEntityManager();
            $services = $em->getRepository('gprsAdminBundle:NameOfService')->findAll();
            $serv = array();
            foreach($services as $s){
                $serv[] = $s->toArray();
            }
            echo json_encode($serv);
            exit;
        }
    }
    
    /**
     * @Route("/api/history_services")
     * @Method({"POST"})
     * @ApiDoc(
     *  description="Get history of services",
     *  input="gprs\ClientBundle\Form\HistoryType"
     * )
     */
    public function HistoryServicesAction(Request $request)
    {
        $logger = $this->getLogger('history');
        $post = $request->request->all();
        $post = $post['history'];

        // Авторизация
        $user = $this->authorizationAction($post,$logger);
        
        if($user && isset($post['icebox_id'])){
            $em = $this->getDoctrine()->getEntityManager();
            if(!isset($post['from'])) $post['from'] = 0;
            if(!isset($post['count'])) $post['count'] = 20;
            $history = $em->getRepository('gprsAdminBundle:Service')->findBy(array('icebox_id'=>$post['icebox_id']),array('id' => 'DESC'),$post['count'],$post['from']);
            $hr = array();
            foreach($history as $h){
                $hr[] = $h->toArray();
            }
            echo json_encode($hr);
            exit;
        }
        $logger->addInfo("Service Error: Not all service params");
        echo json_encode(array('error_code'=>6,'error_message'=>'Not all params'));
        exit;
    }
    
    /**
     * @Route("/api/outlets")
     * @Method({"POST"})
     * @ApiDoc(
     *  description="Get all addresses",
     *  input="gprs\ClientBundle\Form\LoginType"
     * )
     */
    public function OutletsAction(Request $request)
    {
        $logger = $this->getLogger('outlets');
        $post = $request->request->all();
        $post = $post['login'];

        // Авторизация
        $user = $this->authorizationAction($post,$logger);
        
        if($user){
            $em = $this->getDoctrine()->getEntityManager();
            $outlets = $em->getRepository('gprsAdminBundle:Outlet')->findAll();
            $out = array();
            foreach($outlets as $o){
                $out[] = $o->toArray();
            }
            echo json_encode($out);
            exit;
        }
    }
    
    /**
     * @Route("/api/complete")
     * @Method({"POST"})
     * @ApiDoc(
     *  description="Complete",
     *  input="gprs\ClientBundle\Form\CompleteType"
     * )
     */
    public function CompleteAction(Request $request)
    {
        $logger = $this->getLogger('complete');
        $post = $request->request->all();
        $post = $post['complete'];

        // Авторизация
        $user = $this->authorizationAction($post,$logger);
        
        if($user && isset($post['icebox_id']) && isset($post['description'])){
            $em = $this->getDoctrine()->getEntityManager();
            $icebox = $em->getRepository('gprsAdminBundle:Icebox')->findOneBy(array('id'=>$post['icebox_id']));
            if(isset($icebox)){
                $service = new Service();
                $service->setDescription($post['description']);
                $service->setUser($user);
                $service->setIcebox($icebox);
                $em->persist($user);
                $em->persist($icebox);
                $em->persist($service);
                $em->flush();
                
                if(!empty($post['status'])){
                    $icebox->setStatus($post['status']);
                    $em->persist($icebox);
                    $em->flush();
                }
                
                echo 'true';
                exit;
            }
            else {
                $logger->addInfo("Service Error: Not correct Icebox Id");
                echo json_encode(array('error_code'=>5,'error_message'=>'Not correct Icebox Id'));
                exit;
            }
        }
        $logger->addInfo("Service Error: Not all service params");
        echo json_encode(array('error_code'=>6,'error_message'=>'Not all params'));
        exit;
    }
    
    /**
     * @Route("/api/remove")
     * @Method({"POST"})
     * @ApiDoc(
     *  description="Remove",
     *  input="gprs\ClientBundle\Form\HistoryType"
     * )
     */
    public function RemoveAction(Request $request)
    {
        $logger = $this->getLogger('remove');
        $post = $request->request->all();
        $post = $post['history'];

        // Авторизация
        $user = $this->authorizationAction($post,$logger);
        
        if($user && isset($post['icebox_id'])){
            $em = $this->getDoctrine()->getEntityManager();
            $icebox = $em->getRepository('gprsAdminBundle:Icebox')->findOneBy(array('id'=>$post['icebox_id']));
            if(isset($icebox)){
                $icebox->setStatus(6);
                $em->persist($icebox);
                $em->flush();
                echo 'true';
                exit;
            }
            else {
                $logger->addInfo("Service Error: Not correct Icebox Id");
                echo json_encode(array('error_code'=>5,'error_message'=>'Not correct Icebox Id'));
                exit;
            }
        }
        $logger->addInfo("Remove Error: Not all params");
        echo json_encode(array('error_code'=>6,'error_message'=>'Not all params'));
        exit;
    }
}
?>
