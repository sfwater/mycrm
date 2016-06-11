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

class UserType extends AbstractType
{
    var $em;
    public function __construct($doctrine){
        $this->em = $doctrine->getManager();
    }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $data = $options['data'];
        $builder
            ->add("username",TextType::class,array(
                'attr'=>array('class'=>'form-control','placeholder'=>'6-16位英文与数字组合','label'=>'6-16位英文与数字组合'),
                'label'=>'用户名',
                ))
            ->add("nickname",TextType::class,array(
                'attr'=>array('class'=>'form-control','placeholder'=>'中文名称','label'=>'中文名称'),
                'label'=>'昵称',
                ))
            ->add("password",PasswordType::class,array(
                'attr'=>array('class'=>'form-control','placeholder'=>'6-16位密码','label'=>'6-16位密码','id'=>'user_password'),
                'label'=>'密码',
                'required'=> $data->getId() == 0
                ))
            ->add("repassword",PasswordType::class,array(
                'attr'=>array('class'=>'form-control','placeholder'=>'确认密码','label'=>'确认密码','equalTo'=>'#user_password'),
                'label'=>'确认密码',
                'mapped'=>false,
                'required'=>false,
                ))
            ->add('roles',ChoiceType::class,array(
                'attr'=>array('class'=>'form-control','placeholder'=>'选择一个用户组','label'=>'选择一个用户组'),
                'mapped'=>false,
                'label'=>'用户组',
                'choices'=>$this->getAllRoles(),
                'choice_label'=>function($role,$key,$index){
                    return $role->getName();
                },
                'mapped'=>false,
                ))
            ->add("isActive",CheckboxType::class,array(
                'label'=>'默认启用',
                'value'=>1,
                'required'=>false,
                ))
            ->add("email",EmailType::class,array(
                'attr'=>array('class'=>'form-control','placeholder'=>'有效的邮箱地址,可不填写','label'=>'有效的邮箱地址,可不填写'),
                'label'=>'邮箱',
                'required'=>false,
                ))
            ->add("expireTime",TextType::class,array(
                'attr'=>array('class'=>'form-control datepicker','placeholder'=>'账号过期时间，过期后账号将不可用','label'=>'账号过期时间，过期后账号将不可用',
                    'data-date-format'=>'yyyy-mm-dd'
                    ),
                'label'=>'过期时间',
                'required'=>false,
                'data'=>$data->getId() == 0 ? '' : date('Y-m-d', $data->getExpireTime()),
                'mapped'=>false,
                ))
            ;
    }
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Admin\UserBundle\Entity\User',
        ));
    }


    private function getAllRoles(){
        $em = $this->em;
        return $em->getRepository('AdminUserBundle:Role')->findAll();
    }
}



?>