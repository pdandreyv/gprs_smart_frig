<?php

namespace gprs\ClientBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;
use gprs\AdminBundle\Entity\Icebox;

class IceboxFilterType extends AbstractType
{
    public $options;
    public function __construct($em=false,$filter_form=false)
    {
        if($em){
            $country = ($filter_form['country']) ? $filter_form['country'] : '';
            $region = ($filter_form['region']) ? $filter_form['region'] : '';
            $this->options = array(
                //'country' => $em->getArrayCountry(),
                //'region' => $em->getArrayRegion($country), 
                'city' => $em->getArrayCity($region),
                'type' => $em->getArrayField('type'),
                'contragent' => $em->getArrayField('contragent'),
                'model' => $em->getArrayField('model'),
                'address' => $em->getArrayField('address'),
                'title' => $em->getArrayField('title'),
            );
        } else {
            $this->options = array(
                //'country' => array(''=>''),
                //'region' => array(''=>''),
                'city' => array(''=>'')
            );
        }
        /*
        $this->options['disabled_region'] = $this->options['disabled_city'] = array();
        if(!$filter_form['country']){
            $this->options['disabled_region'] = array('disabled'=>'disabled');
        }
        if(!$filter_form['region']){
            $this->options['disabled_city'] = array('disabled'=>'disabled');
        }*/
        //var_dump($this->options['disabled']); exit;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            // Локальное решение для отображения только фильтра города
            /*->add('country', 'choice', array(
                'label' => 'country',
                'choices' => $this->options['country'],
                'required'=>false,
                'attr'=>array('onchange'=>'get_data(this.value,"region")')
            ))
            ->add('region', 'choice', array(
                'label' => 'region',
                'choices' => $this->options['region'],
                'required'=>false,
                'attr'=> array_merge($this->options['disabled_region'],array('onchange'=>'get_data(this.value,"city")'))
            ))*/
            ->add('model', 'choice', array(
                'label' => 'model',
                'choices' => $this->options['model'],
                'required'=>false,
            ))
            ->add('type', 'choice', array(
                'label' => 'type',
                'choices' => $this->options['type'],
                'required'=>false,
            ))
            ->add('contragent', 'choice', array(
                'label' => 'contragent',
                'choices' => $this->options['contragent'],
                'required'=>false,
            ))
            ->add('city', 'choice', array(
                'label' => 'city',
                'choices' => $this->options['city'],
                'required'=>false,
                //'attr'=>$this->options['disabled_city']
            ))
            //->add('type', null, array('label' => 'type'))
            ->add('title', 'choice', array(
                'label' => 'title',
                'choices' => $this->options['title'],
                'required'=>false,
            ))
            ->add('address', 'choice', array(
                'label' => 'address',
                'choices' => $this->options['address'],
                'required'=>false,
            ))
            ->add('status', 'choice', array('label' => 'status','choices' => Icebox::getStatuses()))
            //->add('contragent', null, array('label' => 'contragent'))
            //->add('phone', null, array('label' => 'phone','required'=>false))
            //->add('serial_number', null, array('label' => 'serial_number'))
            //->add('model', null, array('label' => 'model'))
            //->add('producer', null, array('label' => 'producer'))
            //->add('date_producted', null, array('label' => 'date_producted','attr'=>array('class'=>'date')))
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
            ->add('monitor','choice', array(
                'label' => 'monitor',
                'choices' => array(''=>'','1' => 'Yes', '0' => 'No'),
                'required'=>false,
            ))
            //->add('lat', null, array('label' => 'Latitude','attr'=>array('help'=>'For add coordinates, please, click on map.')))
            //->add('lng', null, array('label' => 'Longitude'))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'gprs\AdminBundle\Entity\Icebox',
            'validation_groups' => array('filter'),
            'translation_domain' => 'messages',
        ));
    }

    public function getName()
    {
        return 'icebox';
    }
}
