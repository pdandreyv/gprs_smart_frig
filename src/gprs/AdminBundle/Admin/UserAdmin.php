<?php

namespace gprs\AdminBundle\Admin;
 
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\UserBundle\Admin\Model\UserAdmin as BaseUserAdmin;
 
use Knp\Menu\ItemInterface as MenuItemInterface;
use gprs\AdminBundle\Entity\User;
 
class UserAdmin extends BaseUserAdmin
{
    protected function configureShowField(ShowMapper $showMapper)
    {
        $showMapper
                ->add('id', null, array('label' => 'ID'))
                ->add('username', null, array('label' => 'Username'))
                ->add('email', null, array('label' => 'Email'))
                ->add('last_login', null, array('label' => 'Last login'))
                ->add('roles', null, array('label' => 'Roles'));
    }
 
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with('General')
                ->add('username')
                ->add('email')
                ->add('plainPassword', 'text', array('required' => false))
            ->end()
            ->with('Management')
                ->add('roles', 'choice', array('choices' => User::getRolesNames(),'required'  => true,'multiple'=>true))
                ->add('locked', null, array('required' => false))
                ->add('expired', null, array('required' => false))
                ->add('enabled', null, array('required' => false))
                ->add('credentialsExpired', null, array('required' => false))
            ->end()
        ;
    }
    
//    public function preUpdate($user)
//    {
//        $this->getUserManager()->updateCanonicalFields($user);
//        $this->getUserManager()->updatePassword($user);
//    }
//
//    public function setUserManager()
//    {
//        $this->userManager = new UserManager();
//    }
//
//    /**
//     * @return UserManagerInterface
//     */
//    public function getUserManager()
//    {
//        return $this->userManager;
//    }
    
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
                ->addIdentifier('id')
                ->addIdentifier('username', null, array('label' => 'Username'))
                ->add('email', null, array('label' => 'Email'));
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
                ->add('username', null, array('label' => 'Username'))
                ->add('email', null, array('label' => 'Email'));
    }
/*
    protected function configureSideMenu(MenuItemInterface $menu, $action, AdminInterface $childAdmin = null)
    {
        $menu->addChild(
            $action == 'edit' ? 'Просмотр пользователя' : 'Редактирование пользователя',
            array('uri' => $this->generateUrl(
                $action == 'edit' ? 'show' : 'edit', array('id' => $this->getRequest()->get('id'))))
        );
    }*/
}
?>
