<?php

namespace gprs\ClientBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class EditIceboxType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('login')
            ->add('password')
            ->add('id')
            ->add('serial_number', null, array('label' => 'serial_number'))
            //->add('code_tt', null, array('label' => 'code_tt'))
            ->add('title', null, array('label' => 'title'))
            ->add('type', null, array('label' => 'type'))
            ->add('model', null, array('label' => 'model'))
            //->add('producer', null, array('label' => 'producer'))
            //->add('date_producted', null, array('label' => 'date_producted','attr'=>array('class'=>'date')))
            //->add('image', 'file', array('label' => 'image','data_class' => null,'required' => false))
            //->add('country', null, array('label' => 'country'))
            //->add('region', null, array('label' => 'region'))
            //->add('city', null, array('label' => 'city'))
            ->add('address', null, array('label' => 'address'))
            ->add('contragent', null, array('label' => 'contragent'))
            //->add('phone', null, array('label' => 'phone'))
            //->add('manager', null, array('label' => 'manager'))
            ->add('status')
            ->add('lat')
            ->add('lng')
            ->add('outlet_id')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'gprs\AdminBundle\Entity\Icebox',
            'csrf_protection'   => false,
        ));
    }

    public function getName()
    {
        return 'edit_icebox';
    }
}
