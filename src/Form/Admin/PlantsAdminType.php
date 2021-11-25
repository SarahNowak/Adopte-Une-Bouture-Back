<?php

namespace App\Form\Admin;

use App\Entity\Plants;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;

class PlantsAdminType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                // ne peut pas être null
                'constraints' => new NotBlank()
            ])
            ->add('variety', TextType::class, [
                // ne peut pas être null
                'constraints' => new NotBlank()
            ])
            ->add('difficulty',  IntegerType::class, [
                // ne peut pas être null
                'constraints' => [
                    new NotBlank(),
                    new Range([
                        'min' => 0,
                        'max' => 5,
                    ]),
                    ],
                    // Les contraintes de validation n'impactent que la vérification des données reçues
                    // Si on veut imposer des limites dans le formulaire HTML, il faut ajouter des attributs séparément :
                    'attr' => [
                        'min' => 0,
                        'max' => 5,
                    ],
            ])
            ->add('description',TextareaType::class, [
                // ne peut pas être null
                'constraints' => [
                    new NotBlank(),
                ],
            ]) 
            ->add('image', FileType::class, [
                // n'est pas requis
                'required' => false,
                // n'est pas lié à la bdd
                'mapped' => false,
                'data_class' => null,
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
            ->add('category')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Plants::class,
        ]);
    }
}
