<?php

namespace gprs\AdminBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * NameOfService
 */
class NameOfService
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $type;


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
     * Set title
     *
     * @param string $title
     * @return NameOfService
     */
    public function setTitle($title)
    {
        $this->title = $title;
    
        return $this;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return NameOfService
     */
    public function setDescription($description)
    {
        $this->description = $description;
    
        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set type
     *
     * @param string $type
     * @return NameOfService
     */
    public function setType($type)
    {
        $this->type = $type;
    
        return $this;
    }

    /**
     * Get type
     *
     * @return string 
     */
    public function getType()
    {
        return $this->type;
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
                case 'updated_at':
                    if($this->$method() instanceof \DateTime){
                        $res[$name] = $this->$method()->format('Y-m-d');
                    }else{
                        $res[$name] = '';
                    }
                    break;
                case 'created_at':
                    if($this->$method() instanceof \DateTime){
                        $res[$name] = $this->$method()->format('Y-m-d H:i:s');
                    }else{
                        $res[$name] = '';
                    }
                    break;
                default:
                    $res[$name] = $this->$method();
                    break;
            }
        }
        
        return $res;
    }
}