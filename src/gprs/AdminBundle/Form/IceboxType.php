<?php

namespace gprs\AdminBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use gprs\AdminBundle\Entity\Icebox;
use Doctrine\ORM\EntityRepository;

class IceboxType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('serial_number', null, array('label' => 'serial_number'))
            ->add('code_tt', null, array('label' => 'code_tt'))
            //->add('title', null, array('label' => 'title'))
            ->add('type', null, array('label' => 'type'))
            ->add('model', null, array('label' => 'model'))
            ->add('producer', null, array('label' => 'producer'))
            //->add('date_producted', null, array('label' => 'date_producted','attr'=>array('class'=>'date')))
            //->add('image', 'file', array('label' => 'image','data_class' => null,'required' => false))
            //->add('country', null, array('label' => 'country'))
            //->add('region', null, array('label' => 'region'))
            //->add('city', null, array('label' => 'city'))
            //->add('address', null, array('label' => 'address'))
            ->add('contragent', null, array('label' => 'contragent'))
            //->add('phone', null, array('label' => 'phone'))
            //->add('manager', null, array('label' => 'manager'))
            //->add('status', 'choice', array('label' => 'status','choices' => Icebox::getStatuses()))
            //->add('lat', 'text', array('label' => 'lat','attr'=>array('help'=>'For add coordinates, please, click on map.')))
            //->add('lng', 'text', array('label' => 'lng'))
            ->add('monitor', 'choice', array('label' => 'monitor','choices' => array('0'=>'No','1'=>'Yes')))
            ->add('outlet', 'entity', array(
                'label' => 'outlet',
                'class' => 'gprsAdminBundle:Outlet',
                'empty_value' => '',
                'property' => 'address',
                'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('i')
                                  ->groupBy('i.address')
                                  ->orderBy('i.address', 'ASC');
                 },
                'required'=>false,
            ))
            ->add('trader', 'entity', array(
                'label' => 'trader',
                'class' => 'gprsAdminBundle:Trader',
                'empty_value' => '',
                'property' => 'fio',
                'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('i')
                                  ->groupBy('i.fio')
                                  ->orderBy('i.fio', 'ASC');
                 },
                'required'=>false,
            ))
            ->add('imei', null, array('label' => 'Imei'))
            ->add('iccid', null, array('label' => 'Id Sim','attr'=>array('help' => 'Id Sim, которые приходили: '.implode(', ', Icebox::loadIccid()))))
            ->add('time_report', null, array('label' => 'Time report'))
            //->add('calib_temp', null, array('label' => 'Калибровка температуры','attr'=>array('help'=>'Формат: <показания прибора при максимально допустимой температуре>,<показания прибора при минимально допустимой температуре>:<максиально допустимая температура>,<минимально допустимая температура>')))
            //->add('calib_weight', null, array('label' => 'Калибровка веса','attr'=>array('help'=>'Формат: <показания при полном холодильнике>,<показания при пустом холодильнике>,<показания при критическом весе>:<критический процент наполненности>')))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'gprs\AdminBundle\Entity\Icebox'
        ));
    }

    public function getName()
    {
        return 'icebox';
    }
}
