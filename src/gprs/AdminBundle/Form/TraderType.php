<?php

namespace gprs\AdminBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use gprs\AdminBundle\Entity\Trader;
use Doctrine\ORM\EntityRepository;

class TraderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $alarms = array('weight', 'location', 'temperature');
        $builder
            ->add('fio', null, array('label' => 'fio'))
            ->add('position', null, array('label' => 'position'))
            //->add('description', null, array('label' => 'description'))
            //->add('address', null, array('label' => 'address'))
            ->add('email', null, array('label' => 'email'))
            ->add('phone', null, array('label' => 'phone'))
            ->add('alarm_power', null, array('label' => 'Send alarm for power','required'=>false))
            ->add('alarm_weight', null, array('label' => 'Send alarm for weight','required'=>false))
            ->add('alarm_location', null, array('label' => 'Send alarm for location','required'=>false))
            ->add('alarm_temperature', null, array('label' => 'Send alarm for temperature','required'=>false))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'gprs\AdminBundle\Entity\Trader'
        ));
    }

    public function getName()
    {
        return 'trader';
    }
}
