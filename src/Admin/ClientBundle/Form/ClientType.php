<?php
namespace Admin\ClientBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class ClientType extends AbstractType
{
    var $em;
    public function __construct($doctrine){
        $this->em = $doctrine->getManager();
    }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $data = $options['data'];
        $builder
            ->add("name",TextType::class,array(
                'attr'=>array('class'=>'form-control','placeholder'=>'店面名字','label'=>'店面名字'),
                'label'=>'店面名字',
                ))
            ->add("address",TextType::class,array(
                'attr'=>array('class'=>'form-control','placeholder'=>'地址','label'=>'地址'),
                'label'=>'地址',
                ))
            ->add("contactor",TextType::class,array(
                'attr'=>array('class'=>'form-control','placeholder'=>'店长姓名','label'=>'店长姓名'),
                'label'=>'店长姓名',
                ))
            ->add("contactor",TextType::class,array(
                'attr'=>array('class'=>'form-control','placeholder'=>'负责人姓名','label'=>'负责人姓名'),
                'label'=>'负责人姓名',
            ))
            ->add("area",TextType::class,array(
                'attr'=>array('class'=>'form-control','placeholder'=>'区域','label'=>'区域'),
                'label'=>'区域',
            ))
            ->add("contact",TextType::class,array(
                'attr'=>array('class'=>'form-control','placeholder'=>'座机','label'=>'座机'),
                'label'=>'座机',
                ))
            ->add("mobile",TextType::class,array(
                'attr'=>array('class'=>'form-control','placeholder'=>'手机号','label'=>'手机号'),
                'label'=>'手机号',
            ))
            ->add("note",TextareaType::class,array(
                'attr'=>array('class'=>'form-control','placeholder'=>'备注','label'=>'备注'),
                'label'=>'备注',
                'required'=>false,
                ))
            ->add("wtime",TextType::class,array(
                'attr'=>array('class'=>'form-control datepicker','placeholder'=>'预计维护时间','label'=>'预计维护时间',
                    'data-date-format'=>'yyyy-mm-dd'
                    ),
                'label'=>'计划维护',
                'data'=>($data->getWtime() == 0 || $data->getWtime() == NULL) ? NULL : date('Y-m-d', $data->getWtime()),
                'required'=>false,
                'mapped'=>false,
                ))
            ;
        if( $data->getId() == 0 ){
            $builder->add("protection",CheckboxType::class,array(
                'label'=>'立即保护',
                'value'=>1,
                'required'=>false,
                'mapped'=>false,
                ));
        }
    }
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Admin\ClientBundle\Entity\Client',
        ));
    }
}



?>