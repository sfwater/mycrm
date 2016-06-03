<?php
namespace Admin\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\DateType;

class UserSearchType extends AbstractType
{
    var $router;
    public function __construct($router){
        $this->router = $router;
    }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("name",TextType::class,array(
                'attr'=>array('class'=>'form-control ipt','placeholder'=>'用户名、别名、邮箱','label'=>'用户名、别名、邮箱'),
                'label'=>'名称：',
                'required'=>false,
                'mapped'=>false,
            ))
            ;
    }
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => NULL,
            'method'=>'GET',
            'attr'=>array('class'=>'form-inline searchForm','id'=>'searchForm'),
            'action'=>$this->router->generate('admin_users_index')
        ));
    }
}



?>