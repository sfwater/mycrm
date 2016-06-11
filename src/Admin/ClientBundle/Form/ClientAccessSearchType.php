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

class ClientAccessSearchType extends AbstractType
{
    var $router;
    public function __construct($router){
        $this->router = $router;
    }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $data = $options['data'];
        $entity = isset($data['client_search']) ? $data['client_search'] : NULL;
        $builder
            ->add("name",TextType::class,array(
                'attr'=>array('class'=>'form-control ipt','placeholder'=>'回访内容的关键字','label'=>'回访内容的关键字'),
                'label'=>'关键字：',
                'required'=>false,
                'mapped'=>false,
                'data'=> $entity == NULL ? '' : $entity['name']
            ))
            ;
    }
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => NULL,
            'method'=>'GET',
            'attr'=>array('class'=>'form-inline searchForm','id'=>'searchForm'),
            'action'=>$this->router->generate('admin_client_access_records_index')
        ));
    }
}



?>