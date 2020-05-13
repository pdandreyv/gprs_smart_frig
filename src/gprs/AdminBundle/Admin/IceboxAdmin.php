<?php

namespace gprs\AdminBundle\Admin;
 
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Show\ShowMapper;
 
use Knp\Menu\ItemInterface as MenuItemInterface;
 
class IceboxAdmin extends Admin
{
    protected function configureShowField(ShowMapper $showMapper)
    {
        $showMapper
                ->add('id', null, array('label' => 'ID'))
                ->add('outlet_id', null, array('label' => 'Outlet Id'))
                ->add('trader_id', null, array('label' => 'Trader Id'))
                ->add('serial_number', null, array('label' => 'Serial Number'))
                ->add('model', null, array('label' => 'Model'))
                ->add('title', null, array('label' => 'Title'))
                ->add('description', null, array('label' => 'Description'))
                ->add('type', null, array('label' => 'Type'))
                ->add('producer', null, array('label' => 'Producer'))
                ->add('date_producted', null, array('label' => 'Date Producted'))
                ->add('image', null, array('label' => 'Image'))
                ->add('country', null, array('label' => 'Country'))
                ->add('region', null, array('label' => 'Region'))
                ->add('city', null, array('label' => 'City'))
                ->add('address', null, array('label' => 'Address'))
                ->add('contragent', null, array('label' => 'Contragent'))
                ->add('status', null, array('label' => 'Status'))
                ->add('lat', null, array('label' => 'Lat'))
                ->add('lng', null, array('label' => 'Lng'))
                ->add('phone', null, array('label' => 'Phone'))
                ->add('manager', null, array('label' => 'Manager'))
                ->add('monitor', null, array('label' => 'Monitor'))
                ->add('created_at', null, array('label' => 'Created At'))
                ->add('updated_at', null, array('label' => 'Updated At'))
                ;
    }
 
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
                //->add('outlet_id', null, array('label' => 'Outlet Id'))
                //->add('trader_id', null, array('label' => 'Trader Id'))
                ->add('serial_number', null, array('label' => 'Serial Number'))
                ->add('model', null, array('label' => 'Model'))
                ->add('title', null, array('label' => 'Title'))
                //->add('description', null, array('label' => 'Description'))
                ->add('type', null, array('label' => 'Type'))
                ->add('producer', null, array('label' => 'Producer'))
                //->add('date_producted', null, array('label' => 'Date Producted'))
                //->add('image', null, array('label' => 'Image'))
                ->add('country', null, array('label' => 'Country'))
                ->add('region', null, array('label' => 'Region'))
                ->add('city', null, array('label' => 'City'))
                ->add('address', null, array('label' => 'Address'))
                ->add('contragent', null, array('label' => 'Contragent'))
                ->add('status', null, array('label' => 'Status'))
                ->add('lat', null, array('label' => 'Lat'))
                ->add('lng', null, array('label' => 'Lng'))
                ->add('phone', null, array('label' => 'Phone'))
                ->add('manager', null, array('label' => 'Manager'))
                ->add('monitor', null, array('label' => 'Monitor'))
                ->add('imei', null, array('label' => 'Imei'))
                ->add('iccid', null, array('label' => 'Id Sim'))
                ->add('calib_temp', null, array('label' => 'Калибровка температуры'))
                ->add('calib_weight', null, array('label' => 'Калибровка веса'))
                //->add('created_at', null, array('label' => 'Created At'))
                //->add('updated_at', null, array('label' => 'Updated At'))
                ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
                ->addIdentifier('id')
                //->add('outlet_id', null, array('label' => 'Outlet Id'))
                //->add('trader_id', null, array('label' => 'Trader Id'))
                //->add('serial_number', null, array('label' => 'Serial Number'))
                ->add('model', null, array('label' => 'Model'))
                ->add('title', null, array('label' => 'Title'))
                //->add('description', null, array('label' => 'Description'))
                ->add('type', null, array('label' => 'Type'))
                //->add('producer', null, array('label' => 'Producer'))
                //->add('date_producted', null, array('label' => 'Date Producted'))
                //->add('image', null, array('label' => 'Image'))
                //->add('country', null, array('label' => 'Country'))
                //->add('region', null, array('label' => 'Region'))
                ->add('city', null, array('label' => 'City'))
                ->add('address', null, array('label' => 'Address'))
                //->add('contragent', null, array('label' => 'Contragent'))
                ->add('status', null, array('label' => 'Status'))
                //->add('lat', null, array('label' => 'Lat'))
                //->add('lng', null, array('label' => 'Lng'))
                //->add('phone', null, array('label' => 'Phone'))
                //->add('manager', null, array('label' => 'Manager'))
                //->add('monitor', null, array('label' => 'Monitor'))
                //->add('created_at', null, array('label' => 'Created At'))
                //->add('updated_at', null, array('label' => 'Updated At'))
                ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
                //->add('outlet_id', null, array('label' => 'Outlet Id'))
                //->add('trader_id', null, array('label' => 'Trader Id'))
                //->add('serial_number', null, array('label' => 'Serial Number'))
                ->add('model', null, array('label' => 'Model'))
                ->add('title', null, array('label' => 'Title'))
                //->add('description', null, array('label' => 'Description'))
                ->add('type', null, array('label' => 'Type'))
                //->add('producer', null, array('label' => 'Producer'))
                //->add('date_producted', null, array('label' => 'Date Producted'))
                //->add('image', null, array('label' => 'Image'))
                //->add('country', null, array('label' => 'Country'))
                //->add('region', null, array('label' => 'Region'))
                ->add('city', null, array('label' => 'City'))
                ->add('address', null, array('label' => 'Address'))
                ->add('contragent', null, array('label' => 'Contragent'))
                ->add('status', null, array('label' => 'Status'))
                //->add('lat', null, array('label' => 'Lat'))
                //->add('lng', null, array('label' => 'Lng'))
                //->add('phone', null, array('label' => 'Phone'))
                //->add('manager', null, array('label' => 'Manager'))
                //->add('monitor', null, array('label' => 'Monitor'))
                //->add('created_at', null, array('label' => 'Created At'))
                //->add('updated_at', null, array('label' => 'Updated At'))
                ;
    }
}
?>