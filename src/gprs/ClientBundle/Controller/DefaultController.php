<?php

namespace gprs\ClientBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use gprs\AdminBundle\Entity\Icebox;
use gprs\AdminBundle\Form\IceboxType;
use gprs\ClientBundle\Form\IceboxFilterType;
use gprs\AdminBundle\Entity\Settings;
use gprs\AdminBundle\Form\SettingsType;
use Exporter\Source\CsvSourceIterator;
use Exporter\Source\ArraySourceIterator;

class DefaultController extends Controller
{
    public $icons = array(
        1 => 'img/white-icon.png',
        2 => 'img/green-icon.png',
        3 => 'img/red-icon.png',
        4 => 'img/red2-icon.png',
        5 => 'img/red-icon.png',
        6 => 'img/red-icon.png',
        7 => 'img/red-icon.png',
    );
    public $cip = 60;
    
    public function getTableAction(Request $request)
    {
        $count_rows = 500;
        $post = $request->request->all();
        $em = $this->getDoctrine()->getEntityManager();
	
        $period = array();
        if($post['period']){
            $period['from'] = $post['from'];
            $period['to'] = $post['to'];
        }
        $last_id = false;
        if(isset($post['more'])){
            $last_id = $this->get('session')->get('last_id');
        }
        $res = $em->getRepository('gprsAdminBundle:Data')->findLastData($post['id'],$count_rows,$period,$last_id); 

        $data = array();
        if($res){
            foreach ($res as $row){
                $data[] = $row->toArray();
            }
        }
        $count_data = $em->getRepository('gprsAdminBundle:Data')->getCount($post['id'],$period);
        $isset_more = ($count_rows < $count_data);

        if($isset_more){
            $last_id = $data[count($data)-1]['id'];
            $this->get('session')->set('last_id',$last_id);
        }

        $fields = explode(',',$this->container->getParameter('data_fields'));
        
        // Если полки по умолчанию отображаются, но для определенного холодильника нужно их убрать, то code_tt дает нам об этом знать 0 - нет полок, 1 - есть
        if(array_search('p1',$fields)){
            $icebox = $em->getRepository('gprsAdminBundle:Icebox')->find($post['id']);
            if($icebox->getCodeTt()=='0'){
                for($i=1;$i<=4;$i++)
                    unset($fields[array_search('p'.$i,$fields)]);
            }
        }
        
        $html = '';
        if(!isset($post['more'])) {
            $html .= '<button onclick="location.href=\''.$this->get('router')->generate('report_export', array('format' => 'xls')).'\'">'.$this->__('Export').' xls</button>';
            $html .= '<button onclick="location.href=\''.$this->get('router')->generate('service_history', array('icebox_id' => $post['id'])).'\'">'.$this->__('Service history').'</button>';
            $html .= '<button onclick="location.href=\''.$this->get('router')->generate('get_graphic_temperature', array('icebox_id' => $post['id'])).'\'">'.$this->__('Graphic Temperature').'</button>';
            $html .= '<button onclick="location.href=\''.$this->get('router')->generate('get_graphic_dooropen', array('icebox_id' => $post['id'])).'\'">'.$this->__('Graphic Door Open').'</button>';
            $html .= '<h4>'.$this->__($post['table']).'</h4>
            <ul class="tabs-table">
                <li id="lishpt" onclick="tabs(this,\'shelf-pr\')">'.$this->__('Shelves %').'</li>
                <li id="lishcn" onclick="tabs(this,\'shelf-cn\')">'.$this->__('Shelves qty').'</li>
                <li id="liother" onclick="tabs(this,\'other-prm\')" class="active">'.$this->__('Other').'</li>
            </ul>    
            <table id="sortTable" class="tablesorter">';
        }
        if($data){
            $report_data = array();
            $pd = $pdc = array(); for($i=1;$i<=5;$i++) {$pd[]='p'.$i; $pdc[]='p'.$i.'c';}
            if(!isset($post['more'])) {
                $html .= '<thead>
                    <tr>';
                    foreach($fields as $f){
                        $html .= '<th '.
                                ((in_array($f,$pd))?'class="shelf-pr"':'').
                                ((in_array($f,$pdc))?'class="shelf-cn"':'').
                                ((!in_array($f,$pd) && (!in_array($f,$pdc)) && $f!='created_at')?'class="other-prm"':'').
                                '>'.$this->__($f).'</th>';
                    }
                    $html .= '</tr>
                </thead>

                <tbody>';
            }
            
                $i=0;
                foreach($data as $row){
                $html .= '<tr id="tr'.$row['id'].'" '.(($i%2==0)?'class="pair"':'').'>';
                    foreach($fields as $f){
                        if($f=='location'){
                            $html .= '<td class="other-prm">'.$this->__('on place').'</td>';
                            $report_data[$i][$this->__($f)] = $this->__('on place');
                        }
                        elseif($f=='weight_status'){
                            switch ($row[$f]){
                                // Все хорошо (пустое поле)
                                case 0: $html .= '<td class="other-prm">&nbsp;</td>';
                                    $report_data[$i][$this->__($f)] = '';
                                    break;
                                // OOS наступило
                                case 1: $html .= '<td class="other-prm" style="color:red;">'.$this->__('OOS').'</td>';
                                    $report_data[$i][$this->__($f)] = $this->__('OOS');
                                    break;
                                // OOS не устранен
                                case 2: $html .= '<td class="other-prm">'.$this->__('Not eliminated').'</td>';
                                    $report_data[$i][$this->__($f)] = $this->__('Not eliminated');
                                    break;
                                // Устранен
                                case 3: $html .= '<td class="other-prm">'.$this->__('Eliminated').'</td>';
                                    $report_data[$i][$this->__($f)] = $this->__('Eliminated');
                                    break;
                            }
                        }
                        elseif($f=='p1'||$f=='p2'||$f=='p3'||$f=='p4'||$f=='p5') {
                            $tt = $row[$f];//round($row[$f]*2, -1)/2;
                            $html .= '<td class="shelf-pr">'.$tt.'</td>';
                            $report_data[$i][$this->__($f)] = $tt;
                        }
                        elseif($f=='p1c'||$f=='p2c'||$f=='p3c'||$f=='p4c'||$f=='p5c') {
                            $html .= '<td class="shelf-cn">'.$row[$f].'</td>';
                            $report_data[$i][$this->__($f)] = $row[$f];
                        }
                        elseif($f=='weight') {
                            $tt = ((round($row['p1']*2, -1)/2)+(round($row['p2']*2, -1)/2)+(round($row['p3']*2, -1)/2)+(round($row['p4']*2, -1)/2)+(round($row['p5']*2, -1)/2))/5;
                            $html .= '<td class="other-prm">'.$tt.'</td>';
                            $report_data[$i][$this->__($f)] = $row[$f];
                        }
                        elseif($f!='created_at') {
                            $html .= '<td class="other-prm">'.$row[$f].'</td>';
                            $report_data[$i][$this->__($f)] = $row[$f];
                        }
                        else {
                            $html .= '<td>'.$row[$f].'</td>';
                            $report_data[$i][$this->__($f)] = $row[$f];
                        }
                    }
                    $i++;
                $html .= '</tr>';
                }
                if(!isset($post['more'])) $html .= '</tbody>';
                
                if(isset($post['more'])){
                    $report_data_old = $this->get('session')->get('data');
                    $report_data = array_merge($report_data_old,$report_data);
                }
                $this->get('session')->set('data',$report_data);
        } else {
            $html .= '<tr><td>'.$this->__('No data').'</td></tr>';
        }
        if(!isset($post['more'])) {
            $html .= '</table>'; 
            if($isset_more){
                $html .= '<br><center><button id="button_more" onclick="more()">'.$this->__('more').'</button></center>';
            }
        }
        elseif($count_rows > count($data)){
            $html .= '<script>$("#button_more").remove();</script>';
        }
        echo $html; 
        exit;
    }
    
    public function getHistoryAction(Request $request)
    {
        $post = $request->request->all();
        $em = $this->getDoctrine()->getEntityManager();

        $res = $em->getRepository('gprsAdminBundle:Service')->findBy(array('icebox_id'=>$post['id'])); 

        $data = array();
        if($res){
            foreach ($res as $row){
                $data[] = $row->toArray();
            }
        }
        
        $fields = array('user','description','created_at');
                
        $html = '';
        if(!isset($post['more'])) {
            $html .= '<button onclick="location.href=\''.$this->get('router')->generate('view_icebox', array('id' => $post['id'])).'\'">'.$this->__('Data').'</button>';
            $html .= '<h4>'.$this->__('Service history').'</h4>
                <table id="sortTable" class="tablesorter">';
        }
        if($data){
            $report_data = array();
                $html .= '<thead>
                    <tr>';
                    foreach($fields as $f){
                        $html .= '<th>'.$this->__($f).'</th>';
                    }
                    $html .= '</tr>
                </thead>

                <tbody>';
            $i=0;
            foreach($data as $row){
            $html .= '<tr id="tr'.$row['id'].'" '.(($i%2==0)?'class="pair"':'').'>';
                foreach($fields as $f){
                    if($f=='description') {
                        $html .= '<td style="text-align:left">'.$row[$f].'</td>';
                    }
                    else {
                        $html .= '<td>'.$row[$f].'</td>';
                    }
                    $report_data[$i][$this->__($f)] = $row[$f];
                }
                $i++;
            $html .= '</tr>';
            }
            $html .= '</tbody>';

            $this->get('session')->set('data',$report_data);
        } else {
            $html .= '<tr><td>'.$this->__('No data').'</td></tr>';
        }
        if(!isset($post['more'])) {
            $html .= '</table>'; 
            if($isset_more){
                $html .= '<br><center><button id="button_more" onclick="more()">'.$this->__('more').'</button></center>';
            }
        }

        echo $html; 
        exit;
    }
    
    public function getData($alarm=false)
    {
        $data = array();
        $filter_form = $this->get('session')->get('filter_form');
        $em = $this->getDoctrine()->getEntityManager();
        $icres = $em->getRepository('gprsAdminBundle:Icebox');

        if(empty($filter_form)){
            $res = ($alarm) ? $icres->getAlarms() : $icres->getAll();
            $form = $this->createForm(new IceboxFilterType($icres), new Icebox());
        }
        else {
            $res = $icres->filter($filter_form,$alarm);
            $icebox = new Icebox();
            $icebox->fromArray($filter_form);
            if($icebox->getTrader())
                $em->persist($icebox->getTrader());
            $form = $this->createForm(new IceboxFilterType($icres,$filter_form), $icebox);
        }

        $data['form'] = $form;
        $data['data'] = array();
        if(count($res)){
            if(is_object($res[0])){
                foreach ($res as $row){
                    $data['data'][] = $row->toArray();
                }
            }
            else {
                $data['data'] = $res;
            }
        }   

        return $data;
    }

    // Определение центра карты
    public function getCenter($data=array())
    {
        if(!empty($data) && isset($data[0]['lat']) && isset($data[0]['lng'])) {
            $center = array('lat'=>$data[0]['lat'],'lng'=>$data[0]['lng']);
        }
        else {
            $center = array('lat'=>$this->container->getParameter('map_center_lat'), 'lng'=>$this->container->getParameter('map_center_lng'));
        }
        return $center;
    }
    public function getCountAlarm()
    {
        return $this->getDoctrine()->getEntityManager()->getRepository('gprsAdminBundle:Icebox')->getCountAlarm($this->get('session')->get('filter_form'));
    }
    public function getCountStandart()
    {
        return $this->getDoctrine()->getEntityManager()->getRepository('gprsAdminBundle:Icebox')->getCountStandart($this->get('session')->get('filter_form'));
    }
    public function getAlarms($data)
    {
        $alarms = array();
        $em = $this->getDoctrine()->getEntityManager();
        foreach($data as $val){
            switch ($val['status']){
                // Изменение координат устройства
                case 3:
                    $alarms[] = array(
                        'icebox'=>$val,
                        'location'=>$em->getRepository('gprsAdminBundle:Location')->getLastLocation($val['id']),
                        'message' => 'Coordinates have been changed',
                    );
                    break;
                // Критический вес холодилькика
               /*case 4:
                    $alarms[] = array(
                        'icebox'=>$val,
                        'location'=>array('lat'=>$val['lat'],'lng'=>$val['lng']),
                        'message' => 'Weith is light',
                    );
                    break;
                default : */
            }
        }
        
        return $alarms;
    }
    public function alarmsAction()
    {
        $data = $this->getData(true);
        $m = explode(';',$this->container->getParameter('main_fields'));
        $table_fields = array_map('explode',array_fill(0,count($m),','),$m);
        
        return $this->render('gprsClientBundle:Default:index.html.twig', array(
            'data' => $data['data'],
            'table_fields'=>$table_fields,
            'alarm'=>1,
            'alarms'=>$this->getAlarms($data['data']),
            'count_alarm'=>count($data['data']),
            'count_standart'=>$this->getCountStandart(),
            'count'=>count($data['data']),
            'form' => $data['form']->createView(),
            'icons' => $this->icons,
            'center' => $this->getCenter($data['data']),
            'statuses' => Icebox::getStatuses()
        ));
    }
    
    public function serviceHistoryAction($icebox_id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $icebox = $em->getRepository('gprsAdminBundle:Icebox')->getOne($icebox_id);
        $location = $em->getRepository('gprsAdminBundle:Location')->findBy(array('icebox_id'=>$icebox_id));
        $data = array();
        if($location){
            foreach ($location as $row){
                $data[] = $row->toArray();
            }
        } 
        $fields = explode(',',$this->container->getParameter('view_icebox_fields'));

        
        $history = $em->getRepository('gprsAdminBundle:Service')->findBy(array('icebox_id'=>$icebox_id));

        return $this->render('gprsClientBundle:Default:history.html.twig', array(
            'history' => $history,
            'icebox' => $icebox, 
            'fields'=>$fields,
            'location'=>$data,
            'location_fields'=>array('created_at','lat','lng'),
            'center'=>$this->getCenter(array($icebox)),
            'statuses' => Icebox::getStatuses()
        ));
    }
    public function getGraphicTemperatureAction($icebox_id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $result = $this->getGraphic($icebox_id,'temperature');
        $res = $result['data'];
        
        $data = array();
        if($res){$i=0;
            $cn = count($res);
            $cc = ($cn<10)?1:(($cn<20)?2:(($cn<30)?3:(($cn<61)?6:10)));
            foreach ($res as $row){
                $data['times'][] = ($i%$cc==0)?"'".$row['num_hour']."'":"''";
                $data['t_out'][] = $row['t_out'];
                $data['t_inside'][] = $row['t_inside'];$i++;
            }
            $data['times'] = implode(',', $data['times']);
            $data['values'] = "{
                        name: 'Temperature Out',
                        data: [".implode(',', $data['t_out'])."]
                    },
                    {
                        name: 'Temperature Inside',
                        data: [".implode(',', $data['t_inside'])."]
                    }";
        }
        $fields = explode(',',$this->container->getParameter('view_icebox_fields'));
        
        return $this->render('gprsClientBundle:Default:graphic_temterature.html.twig', array(
            'data' => $data,
            'icebox' => $em->getRepository('gprsAdminBundle:Icebox')->getOne($icebox_id),
            'fields' => $fields,
            'page'=>1,
            'post'=>$result['post'],
            'count_rows'=>$result['count'],
            'cip'=>$this->cip,
            'temp'=>'temperature',
            'temp2'=>'dooropen',
            'name_grafic'=>'Temperature Graph',
            'name_os'=>'Temperature '
        ));
    }
    public function getGraphicDooropenAction($icebox_id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $result = $this->getGraphic($icebox_id,'dooropen');
        $res = $result['data'];
        $period = isset($result['post']['period']) ? $result['post']['period'] : 'all';
        $period = $period == 'all' ? FALSE : TRUE;

        $data = array();
        if($res){$i=0;
            $cn = count($res);
            $cc = ($cn<10)?1:(($cn<20)?2:(($cn<30)?3:(($cn<61)?6:10)));
            foreach ($res as $row){
                $data['times'][] = ($i%$cc==0)?"'".$row['num_hour']."'":"''";
                $data['dooropen'][] = $period ? $row['summ_dooropen'] : $row['dooropen'];$i++;
            }
            $data['times'] = implode(',', $data['times']);
            $data['values'] = "{
                        name: 'Dooropen',
                        data: [".implode(',', $data['dooropen'])."]
                    }";
        }
        $fields = explode(',',$this->container->getParameter('view_icebox_fields'));
        
        return $this->render('gprsClientBundle:Default:graphic_temterature.html.twig', array(
            'data' => $data,
            'icebox' => $em->getRepository('gprsAdminBundle:Icebox')->getOne($icebox_id),
            'fields' => $fields,
            'page'=>1,
            'post'=>$result['post'],
            'count_rows'=>$result['count'],
            'cip'=>$this->cip,
            'temp'=>'dooropen',
            'temp2'=>'temperature',
            'name_grafic'=>'Graph of opening doors',
            'name_os'=>'Count opening doors'
        ));
    }
    
    public function getGraphic($icebox_id,$temp)
    {
        if(isset($_POST['daterange'])) {
            $post = $_POST;
            $post['daterange'] = (array)json_decode($post['daterange']);
        }
        else $post = array('id'=>$icebox_id,'period'=>false,'daterange'=>false,'page'=>1,'graph'=>$temp);
        $from=($post['page']-1)*$this->cip;       
        $em = $this->getDoctrine()->getEntityManager();
        $res = $em->getRepository('gprsAdminBundle:Data')->getFilterData($post,$from,$this->cip);
        $res['post']=$post;

        return $res;
    }
    
    public function indexAction()
    {
        /*$pdd = explode(',', '579,272,595,418,643');
        $calib_count = explode(',', '28,36,36,36,36');
        $cmin = explode(',', '696,463,688,489,834');
        $cmax = explode(',', '572,145,364,207,534');
        for($i=0;$i<5;$i++){
            $count_bottles = ceil(($pdd[$i]-$cmin[$i])/(($cmax[$i]-$cmin[$i])/$calib_count[$i]));
            $bottles[] = ($count_bottles >= 0 && $count_bottles <= $calib_count[$i]) ? $count_bottles : '-' ;
            $weight[] = ($pdd[$i]-$cmin[$i])*100/($cmax[$i]-$cmin[$i]);
        }
        echo "<pre>";var_dump($bottles);var_dump($weight); echo "</pre>"; exit;*/
        $data = $this->getData();
        
        if(isset($data['data'][0]['id'])) {
            $em = $this->getDoctrine()->getEntityManager();
            $icebox = $em->getRepository('gprsAdminBundle:Icebox')->getOne($data['data'][0]['id']);
            $radius = $em->getRepository('gprsAdminBundle:Location')->getLastLocation($icebox['id']);
        }
        
        $m = explode(';',$this->container->getParameter('main_fields'));
        $table_fields = array_map('explode',array_fill(0,count($m),','),$m);

        return $this->render('gprsClientBundle:Default:index.html.twig', array(
            'data' => $data['data'],
            'site' => $this->container->getParameter('site'),
            'table_fields'=>$table_fields,
            'alarm'=>0,
            'alarms'=>$this->getAlarms($data['data']),
            'count_alarm'=>$this->getCountAlarm(),
            'count_standart'=>count($data['data']),
            'count'=>count($data['data']),
            'form' => $data['form']->createView(),
            'icons' => $this->icons,
            'center' => $this->getCenter($data['data']),
            'radius' => isset($radius)?$radius:false,
            'statuses' => Icebox::getStatuses()
        ));
    }
    
    public function filterAction(Request $request)
    {
        $em = $this->getDoctrine()->getEntityManager()->getRepository('gprsAdminBundle:Icebox');
        $icebox = new Icebox();
        $form = $this->createForm(new IceboxFilterType($em), $icebox);
        $post = $request->request->all();
        $page = 'gprs_client_homepage';
        if($post['page']){
            $page = 'alarms';
        }
        if ($request->getMethod() == 'POST') {
            $form->bind($request);

            $form_data = $form->getData()->toArray();
            unset($form_data['id']);
            $this->get('session')->set('filter_form',$form_data);
            if (!$form->isValid()) {
//echo "<pre>"; var_dump($form->getErrorsAsString()); exit;
                $this->get('session')->set('error_form',$form->getErrors());
            }
        }
        
        return $this->redirect($this->generateUrl($page));
    }
        
    public function clearFilterAction($alarm)
    {
        $page = 'gprs_client_homepage';
        if($alarm){
            $page = 'alarms';
        }
        $this->get('session')->set('filter_form','');
        return $this->redirect($this->generateUrl($page));
    }
    
    public function tableAction()
    {
        return $this->render('gprsClientBundle:Default:table.html.twig', array(
            'data' => $data,
            'exeption'=>array('image','lat','lng'),
            'alarms'=>$alarms
        ));
    }
    
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
                $em->persist($icebox);
                $em->flush();
                
                return $this->redirect($this->generateUrl('gprs_client_homepage'));
            }
        }
        
        return $this->render('gprsClientBundle:Default:form.html.twig', array('form' => $form->createView()));
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
                $em->persist($icebox);
                $em->flush();
                
                return $this->redirect($this->generateUrl('gprs_client_homepage'));
            }
        }
        
        return $this->render('gprsClientBundle:Default:form.html.twig', array('form' => $form->createView()));
    }
    
    public function newAction()
    {
        if(!$this->isGranted('ROLE_SUPER_ADMIN')) return $this->redirect($this->generateUrl('gprs_client_homepage'));
        
        $icebox = new Icebox();
        $form = $this->createForm(new IceboxType(), $icebox);
        
        return $this->render('gprsClientBundle:Default:form.html.twig', array('form' => $form->createView(),'center'=>$this->getCenter()));
    }
    
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getEntityManager();
        $icebox = $em->getRepository('gprsAdminBundle:Icebox')->find($id);

        if(!$icebox || !$this->isGranted('ROLE_SUPER_ADMIN')) return $this->redirect($this->generateUrl('gprs_client_homepage'));
        
        $form = $this->createForm(new IceboxType(), $icebox);

        return $this->render('gprsClientBundle:Default:form.html.twig', array('form' => $form->createView(),'center'=>$this->getCenter(array($icebox->toArray()))));
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

        return $this->render('gprsClientBundle:Default:view.html.twig', 
                array(
                    'icebox' => $icebox, 
                    'fields'=>array('serial_number','iccid','title','type','model','city','address','contragent','status','t_out','t_inside','dooropen'),
                    'location'=>$data,
                    'location_fields'=>array('created_at','lat','lng'),
                    'center'=>$this->getCenter(array($icebox)),
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
    public function settingsAction()
    {
        $user = $this->getUser();
        $em = $this->getDoctrine()->getEntityManager();
        $settings = $em->getRepository('gprsAdminBundle:Settings')->findOneBy(array('user_id'=>$user->getId()));
        if($settings){
            $time_report = $settings->getTimeReport();
        }
        if(empty($time_report)) $time_report = 3600;
        $time = gmdate("H:i:s", $time_report);

        if(!$settings){
            $settings = new Settings();
            $settings->setUserId($user->getId());
            $settings->setReportChangeLocation(false);
            $em->persist($settings);
            $em->flush();
        }
        
        $form = $this->createForm(new SettingsType(), $settings);

        $params = array(
            'form' => $form->createView(),
            'time_report'=>$time_report,
            'time'=>$time,
        );
        
        return $this->render('gprsClientBundle:Default:settings.html.twig', $params);
    }
    
    public function reportAction()
    {
        
        return $this->render('gprsClientBundle:Default:report.html.twig', array());
    }
    
    public function settingsUpdateAction(Request $request)
    {
        $user = $this->getUser();
        $em = $this->getDoctrine()->getEntityManager();
        $settings = $em->getRepository('gprsAdminBundle:Settings')->findOneBy(array('user_id'=>$user->getId()));
        $form = $this->createForm(new SettingsType(), $settings);
        $request = $this->getRequest();
        
        if ($request->getMethod() == 'POST') {
            $form->bind($request);

            if ($form->isValid()) {
                $em->persist($settings);
                $em->flush();
                
                return $this->redirect($this->generateUrl('settings'));
            }
        }
        
        return $this->render('gprsClientBundle:Default:settings.html.twig', array('form' => $form->createView()));
    }
    public function settingsTimeAction(Request $request)
    {
        $user = $this->getUser();
        $em = $this->getDoctrine()->getEntityManager();
        $settings = $em->getRepository('gprsAdminBundle:Settings')->findOneBy(array('user_id'=>$user->getId()));

        if(!isset($_POST['time_report']) || empty($_POST['time_report']) || !is_numeric($_POST['time_report']) || $_POST['time_report']<10) $time_report = 3600;
        else $time_report = $_POST['time_report'];
        
        $settings->setTimeReport($time_report);
        $em->persist($settings);
        $em->flush();
        
        return $this->redirect($this->generateUrl('settings'));
    }
    
    public function getRegionAction($country){
        $regions = $this->getDoctrine()->getEntityManager()->getRepository('gprsAdminBundle:Icebox')->getArrayRegion($country);
        foreach ($regions as $r){
            echo "<option value='$r'>$r</option>";
        }
        exit;
    }
    
    public function getCityAction($region){
        $city = $this->getDoctrine()->getEntityManager()->getRepository('gprsAdminBundle:Icebox')->getArrayCity($region);
        foreach ($city as $r){
            echo "<option value='$r'>$r</option>";
        }
        exit;
    }
    
    public function getReportAction(Request $request)
    {
        $post = $request->request->all();
        
        $res = $this->getDoctrine()->getEntityManager()->getRepository('gprsAdminBundle:Icebox')->getReport($post);
        
        // Преобразование данных
        $i = 0;
        foreach($res['data'] as $row){
            foreach($row as $key => $val){
                if ($key=='power'){
                    if ($row['status']==1) $res['data'][$i][$key] = $this->__('not power');
                    else $res['data'][$i][$key] = $this->__('do power');
                }
                elseif ($key=='weight'){
                    if ($row['status']==4) $res['data'][$i][$key] = $this->__('Weith is light');
                    else $res['data'][$i][$key] = $this->__('full');
                }
                elseif ($key=='location'){
                    if ($row['status']==3) $res['data'][$i][$key] = $this->__('Coordinates was changed');
                    else $res['data'][$i][$key] = $this->__('on place');
                }
                elseif ($key=='monitor'){
                    if ($row['monitor']) $res['data'][$i][$key] = $this->__('Yes');
                    else $res['data'][$i][$key] = $this->__('No');
                }

            }
            $i++;
        }
        
        // Формирование html
        $html = '';
        if($res){
            $report_data = array();
            $html = '
                <div class="export">
                    <button onclick="location.href=\''.$this->get('router')->generate('report_export', array('format' => 'xls')).'\'">'.$this->__('Export').' xls</button> 
                    <!--<button onclick="location.href=\''.$this->get('router')->generate('report_export', array('format' => 'csv')).'\'">'.$this->__('Export').' csv</button> -->
                </div><br><br><br>
                <a class="close" onclick="close_report();return false;">'.$this->__('Close').'</a><table class="table"><thead><tr>';

            foreach ($res['fields'] as $f){
                $html .= '<th>'.$this->__($f).'</th>';
            }
            $html .= '</tr></thead><tbody>';
            $i=0;
            foreach ($res['data'] as $rid => $row){
                $html .= '<tr>';
                foreach ($res['fields'] as $field){
                    if($field == 'u_oos'){
                        $value = ($row[$field]=='1')?$this->__('Eliminated'):(($row[$field]=='0')?$this->__('Not eliminated'):'');
                        $html .= '<td class="'.$field.'">'.$value.'</td>';
                        $report_data[$i][$this->__($field)] = $value;
                    }
                    else {
                        $html .= '<td class="'.$field.'">'.$row[$field].'</td>';
                        $report_data[$i][$this->__($field)] = $row[$field];
                    }
                }
                $i++;
                $html .= '</tr>';
            }
            $html .= '</tbody></table><!--<button>'.$this->__('Graphically display').'</button>-->';

            $this->get('session')->set('data',$report_data);
        }
        echo $html;
        exit;
    }
    
    public function exporterAction($format)
    {
        $allowedExportFormats = array('xls','csv');

        if (!in_array($format, $allowedExportFormats) ) {
            throw new \RuntimeException(sprintf('Export in format `%s` is not allowed. Allowed formats are: `%s`', $format, implode(', ', $allowedExportFormats)));
        }

        $filename = sprintf('report_%s.%s',
            date('Y_m_d_H_i_s', strtotime('now')),
            $format
        );

        $data = new ArraySourceIterator($this->get('session')->get('data'));
        
        return $this->get('sonata.admin.exporter')->getResponse($format, $filename, $data);
    }
    
    public function __($str, $ar=array())
    {
        return $this->get('translator')->trans($str, $ar);
    }
    
    public function isGranted($role)
    {
        return $this->container->get('security.context')->isGranted($role);
    }
    
    public function getQrcodeAction($id)
    {
        $file = $this->get('kernel')->getRootDir()."/../web/upload/qrcode".$id.".png";
        if (!file_exists($file)) 
            $res = \PHPQRCode\QRcode::png(base64_encode($id), $file, 'L', 16, 4);

        if (file_exists($file)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename='.basename($file));
            header('Content-Transfer-Encoding: binary');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file));
            readfile($file);
        }
        else echo "No file!";
        exit;
    }
}
