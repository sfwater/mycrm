<?php
namespace Admin\ConsoleBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class SiteConfigType extends AbstractType
{
    var $router;
    public function __construct($router){
        $this->router = $router;
    }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
    }
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => NULL,
            'attr'=>array('class'=>'pageForm required-validate','onsuccess'=>'dialogCallback'),
            'action'=>$this->router->generate('admin_configuration')
        ));
    }
}



?>