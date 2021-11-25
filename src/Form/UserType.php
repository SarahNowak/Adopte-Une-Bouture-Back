<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add('email', EmailType::class, [
            // ne peut pas être null
            // doit être sous la forme d'une email
            'constraints' => [
                new NotBlank(),
                new Email(),
            ]
        ])
        ->add('password', PasswordType::class, [
            'constraints' => [
                // ne peut pas être null
                new NotBlank(),
                // L'argument du constructeur d'une contrainte de validation
                // est toujours un tableau associatif où les options sont nommées en clé
                // On peut toujours personnaliser le message à afficher
                // si la contrainte n'est pas respectée
            ]
        ])
        ->add('pseudo', TextType::class, [
            // ne peut pas être null
            'constraints' => new NotBlank()
        ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
