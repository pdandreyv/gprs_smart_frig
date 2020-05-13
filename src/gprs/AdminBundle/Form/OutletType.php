<?php

namespace gprs\AdminBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use gprs\AdminBundle\Entity\Outlet;
use Doctrine\ORM\EntityRepository;

class OutletType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', null, array('label' => 'title'))
            ->add('type', null, array('label' => 'type'))
            //->add('description', null, array('label' => 'description'))
            ->add('country', null, array('label' => 'country'))
            //->add('region', null, array('label' => 'region'))
            ->add('city', null, array('label' => 'city'))
            ->add('address', null, array('label' => 'address'))
            //->add('manager', null, array('label' => 'manager'))
            //->add('phone', null, array('label' => 'phone'))
            //->add('status', 'choice', array('label' => 'status','choices' => Outlet::getStatuses()))
            ->add('lat', 'text', array('label' => 'lat','attr'=>array('help'=>'For add coordinates, please, click on map.')))
            ->add('lng', 'text', array('label' => 'lng'))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'gprs\AdminBundle\Entity\Outlet'
        ));
    }

    public function getName()
    {
        return 'outlet';
    }
}
