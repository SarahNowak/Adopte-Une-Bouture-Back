<?php

namespace App\Form;

use App\Entity\Ads;
use App\Entity\Category;
use App\Entity\Growth;
use PhpParser\Parser\Multiple;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('plant_ads')
            ->add('city')
            ->add('coordinates')
            ->add('quantity')
            ->add('description')
            ->add('category', EntityType::class, [
                // données récupérées dans la table Category directement de la bdd
                'class' => Category::class,
                // boutons radios
                'multiple' => false,
                'expanded' => true,
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
