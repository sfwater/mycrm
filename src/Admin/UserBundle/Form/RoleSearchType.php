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
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class RoleSearchType extends AbstractType
{
    var $router;
    public function __construct($router){
        $this->router = $router;
    }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $data = $options['data'];
        $entity = isset($data['role_search']) ? $data['role_search'] : NULL;
        $builder
            ->add("name",TextType::class,array(
                'attr'=>array('class'=>'form-control ipt','placeholder'=>'组名、标识','label'=>'组名、标识'),
                'label'=>'组名、标识：',
                'required'=>false,
                'mapped'=>false,
                'data'=> $entity == NULL ? '' : $entity['name'],
            ))
            ;
    }
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => NULL,
            'method'=>'GET',
            'attr'=>array('class'=>'form-inline searchForm','id'=>'searchForm'),
            'action'=>$this->router->generate('admin_roles_index')
        ));
    }
}



?>