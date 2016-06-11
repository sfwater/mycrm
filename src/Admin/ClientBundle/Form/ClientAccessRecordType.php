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

class ClientAccessRecordType extends AbstractType
{
    var $em;
    public function __construct($doctrine){
        $this->em = $doctrine->getManager();
    }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("description",TextareaType::class,array(
                'attr'=>array('class'=>'form-control','placeholder'=>'回访内容','label'=>'回访内容'),
                'label'=>'回访内容',
                ))
            ->add("wtime",TextType::class,array(
                'attr'=>array('class'=>'form-control datepicker','placeholder'=>'预计维护时间','label'=>'预计维护时间',
                    'data-date-format'=>'yyyy-mm-dd'
                    ),
                'label'=>'计划维护',
                'required'=>false,
                'mapped'=>false,
                ))
            ;
    }
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Admin\ClientBundle\Entity\ClientAccessRecord',
        ));
    }
}



?>