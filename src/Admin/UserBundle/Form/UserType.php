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

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("username",TextType::class,array(
                'attr'=>array('class'=>'form-control','placeholder'=>'6-16位英文与数字组合','label'=>'6-16位英文与数字组合')
                ))
            ->add("nickname",TextType::class,array(
                'attr'=>array('class'=>'form-control')
                ))
            ->add("nickname",TextType::class,array(
                'attr'=>array('class'=>'form-control')
                ))
            ->add("password",PasswordType::class,array(
                'attr'=>array('class'=>'form-control')
                ))
            ->add("isActive",CheckboxType::class,array(
                'attr'=>array('class'=>'form-control'),
                'label'=>'默认启用',
                ))
            ->add("roles",ChoiceType::class,array(
                'attr'=>array('class'=>'form-control'),
                'choices'=>array('1'=>true),
                ))
            ->add("email",EmailType::class,array(
                'attr'=>array('class'=>'form-control')
                ))
            ;
    }
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Admin\UserBundle\Entity\User',
        ));
    }
}



?>