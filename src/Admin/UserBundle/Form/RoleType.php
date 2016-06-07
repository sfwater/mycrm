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

class RoleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("name",TextType::class,array(
                'attr'=>array('class'=>'form-control','placeholder'=>'中文组名','label'=>'中文组名'),
                'label'=>'组名',
                ))
            ->add("role",TextType::class,array(
                'attr'=>array('class'=>'form-control','placeholder'=>'用户组标识,必须以ROLE_开头','label'=>'用户组标识,必须以ROLE_开头'),
                'label'=>'用户组标识',
                ))
            ->add("mask",ChoiceType::class,array(
                'choices'=>array(
                    '新增'=>1,
                    '编辑'=>2,
                    '删除'=>4,
                    ),
                'label'=>'权限',
                'multiple'=>true,
                'expanded'=>true,
                'mapped'=>false,
                'label_attr'=>array('class'=>'checkbox-inline'),
                'data'=>array(1,2,4)
                ))
            ;
    }
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Admin\UserBundle\Entity\Role',
        ));
    }
}



?>