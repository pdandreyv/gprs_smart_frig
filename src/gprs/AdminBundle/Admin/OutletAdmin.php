<?php

namespace gprs\AdminBundle\Admin;
 
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Show\ShowMapper;
 
use Knp\Menu\ItemInterface as MenuItemInterface;
 
class OutletAdmin extends Admin
{
    protected function configureShowField(ShowMapper $showMapper)
    {
        $showMapper
                ->add('id', null, array('label' => 'ID'))
                ->add('title', null, array('label' => 'Title'))
                ->add('description', null, array('label' => 'Description'))
                ->add('type', null, array('label' => 'Type'))
                ->add('country', null, array('label' => 'Country'))
                ->add('region', null, array('label' => 'Region'))
                ->add('city', null, array('label' => 'City'))
                ->add('address', null, array('label' => 'Address'))
                ->add('status', null, array('label' => 'Status'))
                ->add('lat', null, array('label' => 'Lat'))
                ->add('lng', null, array('label' => 'Lng'))
                ->add('manager', null, array('label' => 'Manager'))
                ->add('phone', null, array('label' => 'Phone'))
                ->add('created_at', null, array('label' => 'Created At'))
                ->add('updated_at', null, array('label' => 'Updated At'))
                ;
    }
 
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
                ->add('title', null, array('label' => 'Title'))
                ->add('description', null, array('label' => 'Description'))
                ->add('type', null, array('label' => 'Type'))
                ->add('country', null, array('label' => 'Country'))
                ->add('region', null, array('label' => 'Region'))
                ->add('city', null, array('label' => 'City'))
                ->add('address', null, array('label' => 'Address'))
                ->add('status', null, array('label' => 'Status'))
                ->add('lat', null, array('label' => 'Lat'))
                ->add('lng', null, array('label' => 'Lng'))
                ->add('manager', null, array('label' => 'Manager'))
                ->add('phone', null, array('label' => 'Phone'))
                ->add('created_at', null, array('label' => 'Created At'))
                ->add('updated_at', null, array('label' => 'Updated At'))
                ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
                ->addIdentifier('id')
                ->add('title', null, array('label' => 'Title'))
                ->add('description', null, array('label' => 'Description'))
                ->add('type', null, array('label' => 'Type'))
                ->add('country', null, array('label' => 'Country'))
                ->add('region', null, array('label' => 'Region'))
                ->add('city', null, array('label' => 'City'))
                ->add('address', null, array('label' => 'Address'))
                ->add('status', null, array('label' => 'Status'))
                ->add('lat', null, array('label' => 'Lat'))
                ->add('lng', null, array('label' => 'Lng'))
                ->add('manager', null, array('label' => 'Manager'))
                ->add('phone', null, array('label' => 'Phone'))
                //->add('created_at', null, array('label' => 'Created At'))
                //->add('updated_at', null, array('label' => 'Updated At'))
                ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
                ->add('title', null, array('label' => 'Title'))
                ->add('description', null, array('label' => 'Description'))
                ->add('type', null, array('label' => 'Type'))
                ->add('country', null, array('label' => 'Country'))
                ->add('region', null, array('label' => 'Region'))
                ->add('city', null, array('label' => 'City'))
                ->add('address', null, array('label' => 'Address'))
                ->add('status', null, array('label' => 'Status'))
                ->add('lat', null, array('label' => 'Lat'))
                ->add('lng', null, array('label' => 'Lng'))
                ->add('manager', null, array('label' => 'Manager'))
                ->add('phone', null, array('label' => 'Phone'))
                ->add('created_at', null, array('label' => 'Created At'))
                ->add('updated_at', null, array('label' => 'Updated At'))
                ;
    }
}
?>