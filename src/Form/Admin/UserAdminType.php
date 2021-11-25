<?php

namespace App\Form\Admin;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;

class UserAdminType extends AbstractType
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
        ->add('pseudo', TextType::class, [
            // ne peut pas être null
            'constraints' => new NotBlank()
        ])
        ->add('adress', TextType::class, [
            'label' => 'Adresse',
            'required' => false,
            'empty_data' => '',
        ])
        ->add('city', TextType::class, [
            'label' => 'Ville',
        ])
        ->add('avatar', FileType::class, [
            // n'est aps requis
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
        ->add('roles', ChoiceType::class, [
            'choices' => [
                'Administrateur' => 'ROLE_ADMIN',
                'Utilisateur' => 'ROLE_USER',
            ],
            // boutons radios
            'multiple' => false,
            'expanded' => true,
        ])

        ->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) {

            // On récupère l'objet User et le formulaire
            $user = $event->getData();
            $form = $event->getForm();

            // On peut maintenant modifier le formulaire selon nos besoins
            // On souhaite faire en sorte que le champs password soit obligatoire à la création et non requis à la modification d'un User
            if ($user->getId() === null) {

                $required = true;
            } else {
                $required = false;
            }
            $form->add('password', RepeatedType::class, [ // mot de passe répété
                // n'est pas lié à la bdd
                'mapped' => false,
                'type' => PasswordType::class,
                'required' => $required,
                'first_options'  => ['label' => 'Mot de passe'],
                'second_options' => ['label' => 'Répétez le mot de passe'],
                'invalid_message' => 'Les mots de passe doivent être identiques.'
            ]);
        })
        ;

        // On ajoute les Data Transformers à la fin
        // Notre propriété $roles dans User DOIT être un tableau.
        // On souhaite proposer des boutons radio pour sélectionner un role et un seul
        // De ce fait, le ChoiceType nous retourne une string.
        // On doit donc transformer le tableau de User en string
        // et transformer la string du ChoiceType en tableau
        $builder->get('roles')->addModelTransformer(new CallbackTransformer(
            // Transforme la données de l'entité pour le formulaire
            function (array $rolesArray): string {
                // retourne le premier role dans le tableau
                // Si le tableau est vide, l'index 0 n'existe et on retourne une string vide
                return $rolesArray[0] ?? '' ;
            },
            // Transformer la donnée du formulaire pour l'entité
            function (string $roleString): array {
                // retourne la string dans un tableau
                return [$roleString];
            }
        ));
        
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
