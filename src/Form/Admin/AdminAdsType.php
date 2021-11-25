<?php

namespace App\Form\Admin;

use App\Entity\Ads;
use App\Entity\Category;
use App\Entity\Growth;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class AdminAdsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('plant_ads', TextType::class, [
                // ne peut pas être null
                'constraints' => new NotBlank
            ])
            ->add('city', TextType::class, [
                 // ne peut pas être null
                'constraints' => new NotBlank
            ])
            ->add('quantity', IntegerType::class , [
                // Les contraintes de validation n'impactent que la vérification des données reçues
                // Si on veut imposer des limites dans le formulaire HTML, il faut ajouter des attributs séparément :
                'attr' => [
                    'min' => 1
                ]
            ])
            ->add('description', TextareaType::class, [
                 // ne peut pas être null
                'constraints' => new NotBlank
            ])
            ->add('image', FileType::class, [
                // n'est pas requis
                'required' => false,
                // n'est pas mappé avec la bdd
                'mapped' => false,
                'data_class' => null

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
            ->add('category', EntityType::class, [
                // données récupérées dans la table Category directement de la bdd
                'class' => Category::class,
            ])
            ->add('users')
            ->add('growths', EntityType::class, [
                // données récupérées dans la table Growth directement de la bdd
                'class' => Growth::class,
                // boutons radios
                'multiple' => false,
                'expanded' => true
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Ads::class,
        ]);
    }
}
