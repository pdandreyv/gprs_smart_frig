<?php

namespace gprs\AdminBundle\Admin;
 
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Show\ShowMapper;
 
use Knp\Menu\ItemInterface as MenuItemInterface;
 
class TraderAdmin extends Admin
{
    protected function configureShowField(ShowMapper $showMapper)
    {
        $showMapper
                ->add('id', null, array('label' => 'ID'))
                ->add('fio', null, array('label' => 'Fio'))
                ->add('phone', null, array('label' => 'Phone'))
                ->add('created_at', null, array('label' => 'Created At'))
                ;
    }
 
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
                ->add('fio', null, array('label' => 'Fio'))
                ->add('phone', null, array('label' => 'Phone'))
                ->add('created_at', null, array('label' => 'Created At'))
                ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
                ->addIdentifier('id')
                ->add('fio', null, array('label' => 'Fio'))
                ->add('phone', null, array('label' => 'Phone'))
                ->add('created_at', null, array('label' => 'Created At'))
                ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
                ->add('fio', null, array('label' => 'Fio'))
                ->add('phone', null, array('label' => 'Phone'))
                ->add('created_at', null, array('label' => 'Created At'))
                ;
    }
}
?>