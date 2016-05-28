<?php
namespace Admin\UserBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("username","text")
            ->add("password", "password")
            ->add("email","email")
            ->add('save', 'submit', array('label' => 'Create User'));
    }
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Admin\UserBundle\Entity\User',
        ));
    }

    public function getName()
    {
        return 'adminUserBundleUser';
    }
}



?>