<?php
namespace gprs\AdminBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Yaml\Parser;

class GenerateCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        parent::configure();
        
        $this
            ->setName('gprs:generate')
            ->setDescription('Generating Admin files from yml model')
            ->addArgument('name', InputArgument::OPTIONAL, 'Name of yml model')
            ->addOption('yell', null, InputOption::VALUE_NONE, 'If set, the task will yell in uppercase letters')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument('name');
        if ($name) {
            $file = dirname(__FILE__).'/../Resources/config/doctrine/'.$name.'.orm.yml';
            if(file_exists($file)){
                $yaml = new Parser();
                $model_yml = $yaml->parse(file_get_contents($file));
                $fields = array_keys($model_yml["gprs\\AdminBundle\\Entity\\$name"]['fields']);

                $code = "<?php

namespace gprs\AdminBundle\Admin;
 
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Show\ShowMapper;
 
use Knp\Menu\ItemInterface as MenuItemInterface;
 
class ".$name."Admin extends Admin
{
    protected function configureShowField(ShowMapper \$showMapper)
    {
        \$showMapper
                ->add('id', null, array('label' => 'ID'))
                ";
            $code .= $this->getListFields($fields);
            $code .= ";
    }
 
    protected function configureFormFields(FormMapper \$formMapper)
    {
        \$formMapper
                ";
            $code .= $this->getListFields($fields);
            $code .= ";
    }

    protected function configureListFields(ListMapper \$listMapper)
    {
        \$listMapper
                ->addIdentifier('id')
                ";
            $code .= $this->getListFields($fields);
            $code .= ";
    }

    protected function configureDatagridFilters(DatagridMapper \$datagridMapper)
    {
        \$datagridMapper
                ";
            $code .= $this->getListFields($fields);
            $code .= ";
    }
}
?>";
                $filename = dirname(__FILE__).'/../Admin/'.$name.'Admin.php';
                $h = fopen($filename, 'w');
                fwrite($h, $code);
                fclose($h);
                $text = 'File Admin/'.$name.'Admin.php generated good!';
            }
            else {
                $text = 'File '.$file.' not exists';
            }
        } else {
            $text = 'Please input Name of yml';
        }
        
        $file_srv = dirname(__FILE__).'/../Resources/config/services.yml';
        if(file_exists($file_srv))
        {
            $code = "\n\n  gprs.admin.".strtolower($name).":
    class: gprs\\AdminBundle\\Admin\\".$name."Admin
    tags:
      - { name: sonata.admin, manager_type: orm, group: 'New', label: '".$name."' }
    arguments:
      - ~
      - gprs\\AdminBundle\\Entity\\".$name."
      - 'SonataAdminBundle:CRUD'
    calls:
      - [ setTranslationDomain, [gprsAdminBundle]]";
          
            $h = fopen($file_srv, 'a');
                fwrite($h, $code);
                fclose($h);
                
            $text .= "\nIn services.yml was added code of service";
        }

        if ($input->getOption('yell')) {
            $text = strtoupper($text);
        }

        $output->writeln($text);
    }
    
    private function getListFields($fields)
    {
        $str = '';
        foreach($fields as $field){
            $str .= "->add('".$field."', null, array('label' => '".ucwords(str_replace('_', ' ', $field))."'))
                ";
        }
        return $str;
    }
}
?>
