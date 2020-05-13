<?php

namespace gprs\AdminBundle\Entity;
use Sonata\UserBundle\Entity\BaseUser;
use FOS\UserBundle\Model\User as AbstractUser;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Yaml\Parser;

/**
 * User
 */
class User extends BaseUser
{
    /**
     * @var integerFOS\UserBundle\Model\User
     */
    protected $id;

    /**
     * @var string
     */
    private $address;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set address
     *
     * @param string $address
     * @return User
     */
    public function setAddress($address)
    {
        $this->address = $address;
    
        return $this;
    }

    /**
     * Get address
     *
     * @return string 
     */
    public function getAddress()
    {
        return $this->address;
    }
    
    public function getEnabled()
    {
        return $this->enabled;
    }
    
    public function getExpired()
    {
        return $this->expired;
    }
    

    
    public function getLocked()
    {
        return !$this->isAccountNonLocked();
    }
    
    static function getRolesNames()
    {
        $pathToSecurity = dirname(__FILE__).'/../../../../app/config/security.yml';
        $yaml = new Parser();
        $rolesArray = $yaml->parse(file_get_contents($pathToSecurity ));
        $roles = array();
        foreach ($rolesArray['security']['role_hierarchy'] as $value)
        {
            if (!is_array($value))
                $roles[$value] = User::convertRoleToLabel($value);
            else foreach($value as $val){
                $roles[$val] = User::convertRoleToLabel($val);
            }
        }
        return $roles;
    }
    static private function convertRoleToLabel($role)
    {
        $roleDisplay = str_replace('ROLE_', '', $role);
        $roleDisplay = str_replace('_', ' ', $roleDisplay);
        return ucwords(strtolower($roleDisplay));
    }
    
    public function getRoles()
    {
        $roles = $this->roles;
        
        return array_unique($roles);
    }
    
    public function getMethodName($name)
    {
        return implode('',array_map('ucfirst',explode('_',$name)));
    }
    
    public function toArray($exclude = array()){
        $class_vars = get_class_vars(get_class($this));
        $res = array();
        
        foreach ($class_vars as $name => $value) {
            $method = 'get'.$this->getMethodName($name);

            if(in_array($name, $exclude)){
                continue;
            }

            switch ($name) {
                case 'created_at':
                    if($this->$method() instanceof \DateTime){
                        $res[$name] = $this->$method()->format('Y-m-d H:i:s');
                    }else{
                        $res[$name] = '';
                    }
                    break;
                case 'lastLogin':
                    if($this->$method() instanceof \DateTime){
                        $res[$name] = $this->$method()->format('Y-m-d H:i:s');
                    }else{
                        $res[$name] = '';
                    }
                    break;
                case 'credentialsExpired':
                    break;
                default:
                    $res[$name] = $this->$method();
                    break;
            }
        }
                $this->getRoles();
        if(isset($res['roles'])){
            foreach($res['roles'] as &$role){
                $role = self::convertRoleToLabel($role);
            }
        }
        return $res;
    }
}