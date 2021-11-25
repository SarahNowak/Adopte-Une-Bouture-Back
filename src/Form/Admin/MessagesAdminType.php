<?php

namespace App\Form\Admin;

use App\Entity\Ads;
use App\Entity\Messages;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class MessagesAdminType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('content', TextareaType::class, [
                // ne peut pas être null
                'constraints' => new NotBlank()
            ])
            ->add('status', ChoiceType::class, [
                'choices' => [
                    'Actif' => 1,
                    'Inactif' => 2,
                ],
                // boutons radios
                'multiple' => false,
                'expanded' => true,
            ])
            ->add('ads')
            ->add('users')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Messages::class,
        ]);
    }
}
