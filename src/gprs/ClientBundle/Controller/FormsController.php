<?php

namespace gprs\ClientBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use gprs\AdminBundle\Entity\Icebox;
use gprs\AdminBundle\Form\IceboxType;
use gprs\AdminBundle\Entity\Outlet;
use gprs\AdminBundle\Form\OutletType;
use gprs\AdminBundle\Entity\Trader;
use gprs\AdminBundle\Form\TraderType;
use gprs\AdminBundle\Entity\User;
use gprs\AdminBundle\Form\UserType;
use gprs\ClientBundle\Form\IceboxFilterType;
use gprs\AdminBundle\Entity\Settings;
use gprs\AdminBundle\Form\SettingsType;
use Exporter\Source\CsvSourceIterator;
use Exporter\Source\ArraySourceIterator;

class FormsController extends Controller
{
    public $icons = array(
        1 => 'img/white-icon.png',
        2 => 'img/green-icon.png',
        3 => 'img/red-icon.png',
        4 => 'img/red2-icon.png',
        5 => 'img/red-icon.png',
        6 => 'img/red-icon.png',
    );


    // Определение центра карты
    public function getCenter($data=array())
    {
        if(!empty($data) && isset($data[0]['lat']) && isset($data[0]['lng'])) {
            $center = array('lat'=>$data[0]['lat'],'lng'=>$data[0]['lng']);
        }
        elseif(isset($data['lat']) && isset($data['lng'])){
            $center = array('lat'=>$data['lat'],'lng'=>$data['lng']);
        }
        else {
            $center = array('lat'=>$this->container->getParameter('map_center_lat'), 'lng'=>$this->container->getParameter('map_center_lng'));
        }
        return $center;
    }
    
    // *** ICEBOX *** //
    
    public function createAction(Request $request)
    {
        $icebox = new Icebox();
        $form = $this->createForm(new IceboxType(), $icebox);
        
        if ($request->getMethod() == 'POST') {
            $form->bind($request);

            if ($form->isValid()) {
                $em = $this->getDoctrine()->getEntityManager();

                $icebox = $form->getData();
                /*if($form['image']->getData()){
                    $file = $form['image']->getData();
                    $dir = $this->get('kernel')->getRootDir().'/../web/upload';

                    $filename = rand(1, 99999).'.'.$file->getClientOriginalName(); // расширение файла

                    $file->move($dir, $filename);
                    $icebox->setImage($filename);
                }*/
                if($time = $form['time_report']->getData()){
                    $icebox->setTimeOfData($time);
                }
                $icebox->setStatus(1);
                $em->persist($icebox);
                $em->flush();
                
                return $this->redirect($this->generateUrl('gprs_client_homepage'));
            }
        }
        
        return $this->render('gprsClientBundle:Forms:form.html.twig', array('form' => $form->createView()));
    }
    
    public function createFromFileAction(Request $request)
    {
        $icebox = new Icebox();
        
        if ($_FILES) {
            $data = array();
            $obj = new CsvSourceIterator($_FILES['file']["tmp_name"]);
            $obj->rewind();
            while($obj->valid()){
                $data[] = $obj->current();
                $obj->next();
            }

            if($data){
                $em = $this->getDoctrine()->getEntityManager();
                foreach ($data as $row){
                    $Icebox = new Icebox($row);
                    $em->persist($Icebox);
                }
                $em->flush();
                return $this->redirect($this->generateUrl('gprs_client_homepage'));
            }
        }
        
        return $this->redirect($this->generateUrl('new_icebox'));
    }
    
    public function updateAction($id)
    {
        if(!$this->isGranted('ROLE_ADMIN')) return $this->redirect($this->generateUrl('gprs_client_homepage'));
        
        $em = $this->getDoctrine()->getEntityManager();
        $icebox = $em->getRepository('gprsAdminBundle:Icebox')->find($id);
        $form = $this->createForm(new IceboxType(), $icebox);
        $request = $this->getRequest();
        
        if ($request->getMethod() == 'POST') {
            $form->bind($request);

            if ($form->isValid()) {
                /*if($form['image']->getData()){
                    $file = $form['image']->getData();
                    $dir = $this->get('kernel')->getRootDir().'/../web/upload';
                    $filename = rand(1, 99999).'.'.$file->getClientOriginalName(); // расширение файла
                    $file->move($dir, $filename);
                    //$icebox->setStatus(1);
                    $icebox->setImage($filename);
                }*/
                if($time = $form['time_report']->getData()){
                    $icebox->setTimeOfData($time);
                }
                $em->persist($icebox);
                $em->flush();
                
                return $this->redirect($this->generateUrl('gprs_client_homepage'));
            }
        }
        
        return $this->render('gprsClientBundle:Forms:form.html.twig', array('form' => $form->createView()));
    }
    
    public function newAction()
    {
        if(!$this->isGranted('ROLE_ADMIN')) return $this->redirect($this->generateUrl('gprs_client_homepage'));
        
        $icebox = new Icebox();
        $form = $this->createForm(new IceboxType(), $icebox);
        
        return $this->render('gprsClientBundle:Forms:form.html.twig', array('form' => $form->createView(),'center'=>$this->getCenter()));
    }
    
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $icebox = $em->getRepository('gprsAdminBundle:Icebox')->find($id);

        if(!$icebox || !$this->isGranted('ROLE_ADMIN')) return $this->redirect($this->generateUrl('gprs_client_homepage'));
        
        $form = $this->createForm(new IceboxType(), $icebox);

        if($icebox->getOutlet()){
            $center = $icebox->getOutlet()->toArray();
        }
        else $center = $icebox->toArray();
        
        return $this->render('gprsClientBundle:Forms:form.html.twig', array(
            'form' => $form->createView(),
            'center'=>$this->getCenter($center))
        );
    }
    
    public function viewAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $icebox = $em->getRepository('gprsAdminBundle:Icebox')->getOne($id);
        $location = $em->getRepository('gprsAdminBundle:Location')->findBy(array('icebox_id'=>$id));
        $data = array();
        if($location){
            foreach ($location as $row){
                $data[] = $row->toArray();
            }
        } 
        //$location = $em->createQuery('SELECT i FROM gprsAdminBundle:Location i WHERE i.icebox_id=:icebox_id')->setParameter('icebox_id', $id)->getArrayResult();
        $fields = explode(',',$this->container->getParameter('view_icebox_fields'));

        return $this->render('gprsClientBundle:Forms:view.html.twig', 
                array(
                    'icebox' => $icebox, 
                    'fields'=>$fields,
                    'location'=>$data,
                    'location_fields'=>array('created_at','lat','lng'),
                    'center'=>$this->getCenter(array($icebox)),
                    'radius' => $em->getRepository('gprsAdminBundle:Location')->getLastLocation($icebox['id']),
                    'statuses' => Icebox::getStatuses()
                ));
    }
    
    public function deleteAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $icebox = $em->getRepository('gprsAdminBundle:Icebox')->find($id);
        $em->remove($icebox);
        $em->flush();
        
        return $this->redirect($this->generateUrl('gprs_client_homepage'));
    }
        
    public function deactivateAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $icebox = $em->getRepository('gprsAdminBundle:Icebox')->find($id);
        $icebox->setStatus(1);
        $em->flush();
        
        return $this->redirect($this->generateUrl('gprs_client_homepage'));
    }
    
    // *** OUTLET *** //
    
    public function createOutletAction(Request $request)
    {
        $form = $this->createForm(new OutletType(), new Outlet());
        
        if ($request->getMethod() == 'POST') {
            $form->bind($request);

            if ($form->isValid()) {
                $em = $this->getDoctrine()->getEntityManager();

                $outlet = $form->getData();
                $em->persist($outlet);
                $em->flush();
                
                return $this->redirect($this->generateUrl('gprs_client_homepage'));
            }
            else {
                        var_dump($form->getErrorsAsString());
            }
        }
        
        return $this->render('gprsClientBundle:Forms:form_outlet.html.twig', array('form' => $form->createView(),'center'=>$this->getCenter()));
    }
    
    public function updateOutletAction($id)
    {
        if(!$this->isGranted('ROLE_ADMIN')) return $this->redirect($this->generateUrl('gprs_client_homepage'));
        
        $em = $this->getDoctrine()->getEntityManager();
        $outlet = $em->getRepository('gprsAdminBundle:Outlet')->find($id);
        $form = $this->createForm(new OutletType(), $outlet);
        $request = $this->getRequest();
        
        if ($request->getMethod() == 'POST') {
            $form->bind($request);

            if ($form->isValid()) {
                $em->persist($outlet);
                $em->flush();
                
                return $this->redirect($this->generateUrl('gprs_client_homepage'));
            }
        }
        
        return $this->render('gprsClientBundle:Forms:form_outlet.html.twig', array('form' => $form->createView(),'center'=>$this->getCenter()));
    }
    
    public function newOutletAction()
    {
        if(!$this->isGranted('ROLE_ADMIN')) return $this->redirect($this->generateUrl('gprs_client_homepage'));
        
        $outlet = new Outlet();
        $form = $this->createForm(new OutletType(), $outlet);
        
        return $this->render('gprsClientBundle:Forms:form_outlet.html.twig', array('form' => $form->createView(),'center'=>$this->getCenter()));
    }
    
    public function editOutletAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $outlet = $em->getRepository('gprsAdminBundle:Outlet')->find($id);

        if(!$outlet || !$this->isGranted('ROLE_ADMIN')) return $this->redirect($this->generateUrl('gprs_client_homepage'));
        
        $form = $this->createForm(new OutletType(), $outlet);

        return $this->render('gprsClientBundle:Forms:form_outlet.html.twig', array('form' => $form->createView(),'center'=>$this->getCenter(array($outlet->toArray()))));
    }
    
    public function viewOutletAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $outlet = $em->getRepository('gprsAdminBundle:Outlet')->find($id);

        return $this->render('gprsClientBundle:Forms:view_outlet.html.twig', 
                array(
                    'outlet' => $outlet->toArray(), 
                    'fields'=>array('title','type','country','city','address'),
                    'center'=>$this->getCenter($outlet->toArray()),
                ));
    }
    
    public function listOutletAction()
    {
        $em = $this->getDoctrine()->getEntityManager();
        $outlets = $em->getRepository('gprsAdminBundle:Outlet')->findAll();
        $data = array();
	if($outlets)
        foreach($outlets as $t)
            $data[] = $t->toArray();
        
        return $this->render('gprsClientBundle:Forms:list_outlet.html.twig', 
                array(
                    'data' => $data, 
                    'fields'=>array('title','type','country','city','address'),
                    'center'=>$this->getCenter(),
                ));
    }
    
    public function deleteOutletAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $outlet = $em->getRepository('gprsAdminBundle:Outlet')->find($id);
        $em->remove($outlet);
        $em->flush();
        
        return $this->redirect($this->generateUrl('gprs_client_homepage'));
    }
    
    // *** TRADER *** //
    
    public function createTraderAction(Request $request)
    {
        $form = $this->createForm(new TraderType(), new Trader());
        
        if ($request->getMethod() == 'POST') {
            $form->bind($request);

            if ($form->isValid()) {
                $em = $this->getDoctrine()->getEntityManager();

                $trader = $form->getData();
                $em->persist($trader);
                $em->flush();
                
                return $this->redirect($this->generateUrl('view_trader'));
            }
            else {
                var_dump($form->getErrorsAsString());
            }
        }
        
        return $this->render('gprsClientBundle:Forms:form_trader.html.twig', array('form' => $form->createView()));
    }
    
    public function updateTraderAction($id)
    {
        if(!$this->isGranted('ROLE_ADMIN')) return $this->redirect($this->generateUrl('gprs_client_homepage'));
        
        $em = $this->getDoctrine()->getEntityManager();
        $trader = $em->getRepository('gprsAdminBundle:Trader')->find($id);
        $form = $this->createForm(new TraderType(), $trader);
        $request = $this->getRequest();
        
        if ($request->getMethod() == 'POST') {
            $form->bind($request);

            if ($form->isValid()) {
                $em->persist($trader);
                $em->flush();
                
                return $this->redirect($this->generateUrl('view_trader'));
            }
        }
        
        return $this->render('gprsClientBundle:Forms:form_trader.html.twig', array('form' => $form->createView(),'center'=>$this->getCenter()));
    }
    
    public function newTraderAction()
    {
        if(!$this->isGranted('ROLE_ADMIN')) return $this->redirect($this->generateUrl('gprs_client_homepage'));
        
        $form = $this->createForm(new TraderType(), new Trader());
        
        return $this->render('gprsClientBundle:Forms:form_trader.html.twig', array('form' => $form->createView()));
    }
    
    public function editTraderAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $trader = $em->getRepository('gprsAdminBundle:Trader')->find($id);

        if(!$trader || !$this->isGranted('ROLE_ADMIN')) return $this->redirect($this->generateUrl('view_trader'));
        
        $form = $this->createForm(new TraderType(), $trader);

        return $this->render('gprsClientBundle:Forms:form_trader.html.twig', array('form' => $form->createView()));
    }
    
    public function viewTraderAction()
    {
        $em = $this->getDoctrine()->getEntityManager();
        $traders = $em->getRepository('gprsAdminBundle:Trader')->findAll();
        $data = array();
        foreach($traders as $t)
            $data[] = $t->toArray();

        return $this->render('gprsClientBundle:Forms:view_trader.html.twig', 
                array(
                    'data' => $data, 
                    'fields'=>array('fio','position','phone','email','alarm_power','alarm_weight','alarm_location','alarm_temperature'),
                ));
    }
    
    public function deleteTraderAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $trader = $em->getRepository('gprsAdminBundle:Trader')->find($id);
        $em->remove($trader);
        $em->flush();
        
        return $this->redirect($this->generateUrl('gprs_client_homepage'));
    }
    
    
    // *** USERS *** //
    
    public function createUserAction(Request $request)
    {
        $form = $this->createForm(new UserType(), new User());
        
        if ($request->getMethod() == 'POST') {
            $form->bind($request);

            if ($form->isValid()) {
                $em = $this->getDoctrine()->getEntityManager();

                $user = $form->getData();
                $em->persist($user);
                $em->flush();
                
                return $this->redirect($this->generateUrl('view_user'));
            }
            else {
                var_dump($form->getErrorsAsString());
            }
        }
        
        return $this->render('gprsClientBundle:Forms:form_user.html.twig', array('form' => $form->createView()));
    }
    
    public function updateUserAction($id)
    {
        if(!$this->isGranted('ROLE_ADMIN')) return $this->redirect($this->generateUrl('gprs_client_homepage'));
        
        $em = $this->getDoctrine()->getEntityManager();
        $user = $em->getRepository('gprsAdminBundle:User')->find($id);
        $form = $this->createForm(new UserType(), $user);
        $request = $this->getRequest();
        
        if ($request->getMethod() == 'POST') {
            $form->bind($request);

            if ($form->isValid()) {
                $em->persist($user);
                $em->flush();
                
                return $this->redirect($this->generateUrl('view_user'));
            }
        }
        
        return $this->render('gprsClientBundle:Forms:form_user.html.twig', array('form' => $form->createView(),'center'=>$this->getCenter()));
    }
    
    public function newUserAction()
    {
        if(!$this->isGranted('ROLE_ADMIN')) return $this->redirect($this->generateUrl('gprs_client_homepage'));
        
        $form = $this->createForm(new UserType(), new User());
        
        return $this->render('gprsClientBundle:Forms:form_user.html.twig', array('form' => $form->createView()));
    }
    
    public function editUserAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $user = $em->getRepository('gprsAdminBundle:User')->find($id);

        if(!$user || !$this->isGranted('ROLE_ADMIN')) return $this->redirect($this->generateUrl('view_user'));
        
        $form = $this->createForm(new UserType(), $user);

        return $this->render('gprsClientBundle:Forms:form_user.html.twig', array('form' => $form->createView()));
    }
    
    public function viewUserAction()
    {
        $em = $this->getDoctrine()->getEntityManager();
        $users = $em->getRepository('gprsAdminBundle:User')->findAll();
        $data = array();
        foreach($users as $t)
            $data[] = $t->toArray();

        return $this->render('gprsClientBundle:Forms:view_user.html.twig', 
                array(
                    'data' => $data, 
                    'fields'=>array('firstname','lastname','username','email','phone','enabled','locked','lastLogin','roles'),
                ));
    }
    
    public function deleteUserAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $user = $em->getRepository('gprsAdminBundle:User')->find($id);
        $em->remove($user);
        $em->flush();
        
        return $this->redirect($this->generateUrl('gprs_client_homepage'));
    }
    
    //****    OTHER    ****//
    
    public function __($str, $ar=array())
    {
        return $this->get('translator')->trans($str, $ar);
    }
    
    public function isGranted($role)
    {
        return $this->container->get('security.context')->isGranted($role);
    }
    
    public function timer($id,$time,$post=0,$msg='',$time_post=20,$last=false)
    {
        $code = '
            <script>
                i = 0;
                cnt = '.$time.';
                width = 550;
                function get_time(i) {
                    sec = cnt - i;
                    m = Math.floor(sec/60);
                    if(m<10) m = "0"+m;
                    s = sec%60;
                    if(s<10) s = "0"+s;
                    return m+":"+s;
                }
                function fn_timer(){
                    i++;
                    $("#bar").css("width",i*width/cnt);
                    $("#time").html(get_time(i));';
                    
                    if($post && $time_post < $time){
                        $code .= '
                            if(i%'.$time_post.'==0) {
                                $.post("'.$this->generateUrl('calib_post', array('id'=>$id)).'",function( data ) {
                                    if(data=="true"){
                                        clearInterval(timer);
                                        $("#btn").html("<button onclick=\'calib()\'>'.$this->__('next').'</button>&nbsp;&nbsp;&nbsp;&nbsp;");
                                        $("#message").html("'.$this->__('empty_weight_icebox').'");
                                    }
                                });
                            }
                        ';
                    }
        
                    $code .='if(i==cnt) {
                        clearInterval(timer);
                        $.post("'.$this->generateUrl('calib_post', array('id'=>$id)).'",function( data ) {
                            if(data=="true"){
                                '.($msg?'$("#message").html("'.$msg.'");':'').'
                                '.($last?'$("#btn").html("<button onclick=\"location.href=\''.$this->generateUrl('calib_cancel', array('id'=>$id)).'\'\">'.$this->__('Finish').'</button>");':'$("#btn").html("<button onclick=\'calib()\'>'.$this->__('next').'</button>&nbsp;&nbsp;&nbsp;&nbsp;");').'
                            }
                            else {
                                $("#message").html("'.$this->__('During this calibration fails').'");
                                $("#btn").html("<button onclick=\'calib()\'>'.$this->__('Repeat').'</button>&nbsp;&nbsp;&nbsp;&nbsp;");
                            }
                        });
                        
                    }
                }
                timer = setInterval(fn_timer, 1000);
            </script> <br>
            <div id="time">00:00</div>
            <div id="timer"><div id="bar"></div></div><br><br>
            <span id="btn"></span>
        ';
        
        return $code;
    }
    
    public function calibAction($id)
    {
        // Постоянные
        $time_calib = 60; // Количество секунд на калибровку одного веса
        $time_data = 20; // Кол. сек. между сеансами связи с холодильником при калибровке
        
        $logger = new Logger('api_data');
        $logger->pushHandler(new StreamHandler($this->get('kernel')->getRootDir().'/logs/calibrate.log', Logger::DEBUG));
        
        $em = $this->getDoctrine()->getEntityManager();
        $icebox = $em->getRepository('gprsAdminBundle:Icebox')->find($id);
        $mess = $btns = '';
        if($icebox){
            $calib_step = $this->get('session')->get('calib_step');
            $slug = ($calib_step)? $calib_step : 'step1';
            $step = substr($slug,-1);
            if($step > 2){
                if(!$this->calib($icebox->getCalibWeight())){
                    $slug = 'step'.($step-1);
                }
            }
            switch ($slug) {
                case 'step1':
                    $logger->addInfo('Begin Calib icebox id='.$id);
                    $icebox->setCalibWeight('a'); // a - начало калибровки
                    if ($icebox->setTimeOfData($time_data)) {
                        $mess = $this->__('Enabled calibration weight');
                        $btns = '<button onclick="calib()">'.$this->__('next').'</button>&nbsp;&nbsp;&nbsp;&nbsp;';
                        $this->get('session')->set('calib_step','step2');
                    }
                    else {
                        $mess = $this->__('Error').': '.$this->__('This icebox is impossible to calibrate');
                        $logger->addInfo('This icebox is impossible to calibrate (id='.$id.')');
                    }
                    break;
                case 'step2':
                    $mess = $this->__('Go check connection with fridge. Wait a few minutes');
                    $btns = $this->timer($id,$time_calib,1,'',$time_data);
                    $this->get('session')->set('calib_step','step3');
                    $logger->addInfo('Check connection icebox id='.$id);
                    break;
                case 'step3':
                    $mess = $this->__('Calibration running refrigerator. Please wait');
                    $btns = $this->timer($id,$time_calib,0,$this->__('Minimum weight of the refrigerator is installed').$this->__('critical_weight_icebox'));
                    $icebox->setCalibWeight($icebox->getCalibWeight().',b');
                    $this->get('session')->set('calib_step','step4');
                    $logger->addInfo('Minimum weight is installed icebox id='.$id);
                    break;
                case 'step4':
                    $mess = $this->__('Calibration running refrigerator. Please wait');
                    $btns = $this->timer($id,$time_calib,0,$this->__('Critical mass of the refrigerator is installed').$this->__('full_weight_icebox'));
                    $icebox->setCalibWeight($icebox->getCalibWeight().',c');
                    $this->get('session')->set('calib_step','step5');
                    $logger->addInfo('Critical mass is installed icebox id='.$id);
                    break;
                case 'step5':
                    $mess = $this->__('Calibration running refrigerator. Please wait');
                    $btns = $this->timer($id,$time_calib,0,$this->__('Weight full refrigerator saved'),1,1);
                    $icebox->setCalibWeight($icebox->getCalibWeight().',d');
                    $this->get('session')->set('calib_step','');
                    $logger->addInfo('Weight full is installed icebox id='.$id);
                    break;
                case 'cancel':
                    $icebox->finishedCalibrate();
                    $icebox->setTimeOfData($this->container->getParameter('time_data'));
                    $logger->addInfo('Calibrate of finished icebox id='.$id);
                    // a,1457:1376,1457:1376,1457:1376,b,1457:1376,1457:1376,1457:1376,c,1457:1376,1457:1376,1457:1376,1457:1376,1457:1376,d,1457:1376,1457:1376,1457:1376,1457:1376
                    $em->persist($icebox);
                    $em->flush();
                    echo 'exit';
                    exit;
                    break;
                default :
                    $mess = $this->__('Error').': '.$this->__('Not correct slug');
                    $logger->addInfo('Not correct slug icebox id='.$id);
            }
            $em->persist($icebox);
            $em->flush();
        }
        else {
            $mess = $this->__('Error').': '.$this->__('Not correct id of Icebox');
            $logger->addInfo('Not correct id of Icebox id='.$id);
        }
        $mess = '<div id="message">'.$mess.'</div>';
        $btns = $btns.'<button onclick="location.href=\''.$this->generateUrl('calib_cancel', array('id'=>$id)).'\'">'.$this->__('Сancel').'</button>';
        
        echo $mess.$btns;
        exit;
    }
    
    public function calibCancelAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $icebox = $em->getRepository('gprsAdminBundle:Icebox')->find($id);
        if($icebox){
            $icebox->finishedCalibrate();
            $icebox->setTimeOfData($this->container->getParameter('time_data'));
            $em->persist($icebox);
            $em->flush();
        }
        $this->get('session')->set('calib_step','');
        
        return $this->redirect($this->generateUrl('edit_icebox',array('id'=>$id)));
    }
    
    public function calib($str)
    {
        $c = explode(',', $str);
        $a = array('d','c','b','a');
        foreach ($a as $aa){
            if(in_array($aa, $c)) {
                $ar = array_keys($c, $aa);
                if(count(array_slice($c, $ar[0])) >= 2) {
                    return true;
                }
                break;
            }
        }
        return false;
    }
    
    public function calibPostAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $icebox = $em->getRepository('gprsAdminBundle:Icebox')->find($id);
        if($icebox){
            if($this->calib($icebox->getCalibWeight())) echo 'true';
        }
        exit;
    }
    
    public function rememberMaxminAction(Request $request)
    {
        $post = $request->request->all();
        
        if(isset($post['polka']) && isset($post['fmaxmin']) && isset($post['id'])){
            $em = $this->getDoctrine()->getEntityManager();
            $icebox = $em->getRepository('gprsAdminBundle:Icebox')->find($post['id']);
            $last_data = $em->getRepository('gprsAdminBundle:Data')->findLast($post['id']);
            if($post['polka']==0){
                $calib = $icebox->getCalibWeight();
                if($calib){
                    $calib_w = explode(':', $calib);
                    $calib_w1 = (count($calib_w)==2)?explode(',', $calib_w[0]):false;
                    if(count($calib_w1) == 3){
                        if($post['fmaxmin']=='max'){
                            $calib = $calib_w1[0].','.$calib_w1[1].','.$last_data['par1'].':'.$calib_w[1];
                        }
                        else {
                            $calib = $last_data['par1'].','.$calib_w1[1].','.$calib_w1[2].':'.$calib_w[1];
                        }
                    }
                }
                else {
                    if($post['fmaxmin']=='max'){
                        $calib = '0,0,'.$last_data['par1'].':0,0,100';
                    }
                    else {
                        $calib = $last_data['par1'].',0,0:0,0,100';
                    }
                }
                $icebox->setCalibWeight($calib);
            }
            else {
                $calib = $icebox->getCalibShelf();
                if(!$calib){
                    $calib = '0,0,0,0,0,0:0,0,0,0,0,0';
                }
                $calib_w = explode(':', $calib);
                $polka = $post['polka']-1;
                if($post['fmaxmin']=='max'){
                    $calib_w1 = explode(',', $calib_w[1]);
                    $calib_w1[$polka] = $last_data['p'.$post['polka'].'d'];
                    $calib = $calib_w[0].':'.$calib_w1[0].','.$calib_w1[1].','.$calib_w1[2].','.$calib_w1[3].','.$calib_w1[4].','.$calib_w1[5];
                }
                else {
                    $calib_w1 = explode(',', $calib_w[0]);
                    $calib_w1[$polka] = $last_data['p'.$post['polka'].'d'];
                    $calib = $calib_w1[0].','.$calib_w1[1].','.$calib_w1[2].','.$calib_w1[3].','.$calib_w1[4].','.$calib_w1[5].':'.$calib_w[1];
                }
                $icebox->setCalibShelf($calib);
                
                // Калибровка количества бутылок
                $calib_count = $icebox->getCalibCount();
                if(!$calib_count){
                    $calib_count = '0,0,0,0,0,0';
                }
                $calib_c = explode(',', $calib_count);
                $calib_c[$polka] = $post['count_bottles'][($polka+1)];
                $calib = $calib_c[0].','.$calib_c[1].','.$calib_c[2].','.$calib_c[3].','.$calib_c[4].','.$calib_c[5];
                $icebox->setCalibCount($calib);
            }
            $em->persist($icebox);
            $em->flush();
            
            echo ($post['fmaxmin']=='max') ? $this->__('Maximum') : $this->__('Minimum');
            echo " ".$this->__('was saved')." ";
            echo ($post['polka']==0) ? $this->__('in the refrigerator all') : $this->__('at shelf')." ".$post['polka'];
            exit;
        }
        echo $this->__('Error'); exit;
    }
}
