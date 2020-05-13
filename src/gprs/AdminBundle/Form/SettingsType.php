<?php

namespace gprs\AdminBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class SettingsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('period_send_sms', null, array('label' => 'Period send sms in minutes'))
            ->add('report_change_location', null, array('label' => 'Send report if location was changed'))
            ->add('report_phone', null, array('label' => 'Report to phone'))
            ->add('emails', null, array('label' => 'Additional emails for reports'))
            ->add('check_icebox', null, array('label' => 'Check icebox'))
            ->add('shelf_limit', null, array('label' => 'Shelf limit'))
            ->add('icebox_limit', null, array('label' => 'Limit OOS'))
            ->add('max_out_temperature', null, array('label' => 'Max outside temperature'))
            ->add('max_in_temperature', null, array('label' => 'Max inside temperature'))
            
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'gprs\AdminBundle\Entity\Settings'
        ));
    }

    public function getName()
    {
        return 'settings';
    }
}
