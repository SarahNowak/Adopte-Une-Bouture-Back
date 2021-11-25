<?php

namespace App\Controller\Api;

use App\Entity\User;
use App\Form\CityType;
use App\Form\UserType;
use App\Repository\UserRepository;
use App\Service\ImageUploader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Annotation (ligne 17 à 21) préfixant la route de toutes les méthodes du controleur
 * 
 * @Route("/api/user", name="api_user_")
 */
class UserController extends AbstractController
{
    /**
     * Method HTTP à préciser
     * 
     * @Route("", name="browse", methods={"GET"})
     */
    public function browse(UserRepository $userRepository, Request $request): Response
    {
        // permet de recuperer la liste de tous les utilisateurs
        $users = $userRepository->findAll();
        
        // on lui donne l'objet a serializer, le statut de reponse 200 ou (Response::HTTP_OK), un tableau vide correspondant aux entetes, le 4eme argument sont les elements de contextes pour lui dire comment serializer : selon quel group, ici avec user_browse
        // on a mis dans entity User @Groups dans les annotations (pour ne pas avoir d'erreur de référence circulaire)
        // cela nous retourne les infos via insomnia
        // Attention a chaque ajout d'annotation faire un bin/console cache:clear
        return $this->json($users, Response::HTTP_OK, [],[
            'groups' => ['user_browse']
        ]);
    }

    /**
     * Methode listant un utilisateur
     * 
     * @Route("/{id}", name="read", methods={"GET"}, requirements={"id"="\d+"})
     */
    public function read(User $user) // on donne au param converter (entre parenthese), l'objet concerné, ici $user. l'ID est récupéré directement dans le nom de la route
    {
        // On restreint l'acces via le denyAccessUnlessGranted qui va aller chercher les informations situés dans le dossier src/Security/Voter, le voter concerné 'user-read' et verifier si l'accès est autorisé
        $this->denyAccessUnlessGranted('USER_READ', $user);
        
        // on retourne au format JSON les informations de cette objet (1: objet , 2: réponse HTTP, 3: tableau des headers, 4: annotation de group @groups)
        return $this->json($user, Response::HTTP_OK, [],[
            'groups' => ['user_read']
        ]);
    }

    /**
     * Methde permettant de récupérer les informations de l'utilisateur connecté
     * 
     * @Route("/current", name="current", methods={"GET"}, requirements={"id"="\d+"})
     */
    public function current(UserInterface $user) // grace au UserInterface, on peut récupérer les informations de l'utilisateur connecté. La requête est effectué sur le endpoint 'current' ce qui permet au front d'obtenir toutes les données concernant le User
    {
        // on retourne au format JSON les informations de cette objet (1: objet , 2: réponse HTTP, 3: tableau des headers, 4: annotation de group @groups)
        return $this->json($user, Response::HTTP_OK, [],[
            'groups' => ['user_read']
        ]);
    }


    /**
     * Methode permettant d'ajouter un utilisateur
     * 
     * @Route("/create_user", name="add", methods={"POST"})
     */
    public function add(Request $request, UserPasswordHasherInterface $userPasswordHasherInterface)
    {
        // on crée notre nouvel objet User
        $user = new User();
        
        // on crée notre formulaire, auquel on associe 1: son type, 2: l'objet concerné, 3: on enleve la verification automatique du CSRF_protection  
        $form = $this->createForm(UserType::class, $user, ['csrf_protection' => false]);
        // on récupère le contenu de la requête qui est au format JSON
        $json = $request->getContent();
        // on décode $json pour transforme les données en tableau associatif 
        $jsonArray = json_decode($json, true);
        // On envoie notre tableau associatif à notre formulaire
        // La méthode submit va faire un peu comme handleRequest et prendre chacune
        // des clées du tableau pour les associer aux inputs du formulaire
        // Après cette étape, notre objet $user sera automatiquement rempli
        // Ça nous permet d'associer automatiquement/facilement des données reçues à notre objet $user
        // De plus, on profite du système de validations selon les contraintes dans nos champs de formulaire
        $form->submit($jsonArray);

        // On vérifie qu'il n'y a pas d'erreur dans le formulaire
        // Normalement on a pas besoin de $form->isSubmitted(), on est sur que le form est envoyé car
        // on a exécuté nous même la méthode submit()
        if ($form->isValid()) {
            // on récupére les données du champ password dans le formulaire
            $newPassword = $form->get('password')->getData();
            //si un mot de passe est récupéré
            if ($newPassword != null) {
                // on encode le mot de passe
                $encodePassword = $userPasswordHasherInterface->hashPassword($user, $newPassword);
                // On attribue à la propriété $password de notre User le mot de passe encodé
                $user->setPassword($encodePassword);
            }
            // on appelle l'entity manager qui permet de manipuler les données en BDD
            $em = $this->getDoctrine()->getManager();
            // on persist le nouvel objet ads, on le place en file d'attente avant enregistrement
            $em->persist($user);
            // on prend les objets modifiés (en file d'attente) et on les enregistre dans la BDD
            $em->flush();
            
            // On est dans une API, on renvoit l'objet sérialisé pour confirmer son ajout
            // en précisant un code 201 Created
            return $this->json($user, Response::HTTP_CREATED, [], [
                'groups' => ['user_read'],
            ]);
        }
        // Si le formulaire n'est pas valide, on doit fournir une réponse avec les messages d'erreurs
        // Tous les messages d'erreurs sont dans $form->getErrors()
        // avec le booléen true, on précise qu'on veut la liste de toutes les erreurs de tous les champs du formulaire
        // Il est possible de parser notre ERROR en une string
        $errorsString = (string) $form->getErrors(true);

        // on retourne le code erreur (BAD REQUEST) avec les erreurs
        return $this->json([
            'errors' => $errorsString,
        ], Response::HTTP_BAD_REQUEST);
    }

    /**
     * Méthode permettant l'enregistrement de la ville de l'utilisateur
     * 
     * @Route("/{id}/city", name="editCity", methods={"POST","PUT","PATCH"}, requirements={"id"="\d+"})
     */
    public function editCity(Request $request, User $user)
    {
        // On restreint l'acces via le denyAccessUnlessGranted qui va aller chercher les informations situés dans le dossier src/Security/Voter, le voter concerné 'user-edit' et verifier si l'accès est autorisé
        $this->denyAccessUnlessGranted('USER_EDIT', $user);
        // on crée notre formulaire, auquel on associe 1: son type, 2: l'objet concerné, 3: on enleve la verification automatique du CSRF_protection
        $form = $this->createForm(CityType::class, $user, ['csrf_protection' => false]);
        // on récupère le contenu de la requête qui est au format JSON 
        $json = $request->getContent();
        // on décode $json pour transforme les données en tableau associatif
        $jsonArray = json_decode($json, true);
        // On envoie notre tableau associatif à notre formulaire
        // La méthode submit va faire un peu comme handleRequest et prendre chacune
        // des clées du tableau pour les associer aux inputs du formulaire
        // Après cette étape, notre objet $user sera automatiquement rempli
        // Ça nous permet d'associer automatiquement/facilement des données reçues à notre objet $user
        // De plus, on profite du système de validations selon les contraintes dans nos champs de formulaire
        $form->submit($jsonArray);
        
        // On vérifie qu'il n'y a pas d'erreur dans le formulaire
        // Normalement on a pas besoin de $form->isSubmitted(), on est sur que le form est envoyé car
        // on a exécuté nous même la méthode submit()
        if ($form->isValid()) {
            // on appelle l'entity manager qui permet de manipuler les données en BDD
            $em = $this->getDoctrine()->getManager();
            // on prend les objets modifiés (en file d'attente) et on les enregistre dans la BDD
            $em->flush();
            
            // On est dans une API, on renvoit l'objet sérialisé pour confirmer son ajout
            // en précisant un code 200 OK
            return $this->json($user, Response::HTTP_OK, [], [
                'groups' => ['user_read'],
            ]);
        }
        // Si le formulaire n'est pas valide, on doit fournir une réponse avec les messages d'erreurs
        // Tous les messages d'erreurs sont dans $form->getErrors()
        // avec le booléen true, on précise qu'on veut la liste de toutes les erreurs de tous les champs du formulaire
        // Il est possible de parser notre ERROR en une string
        $errorsString = (string) $form->getErrors(true);

        // on retourne le code erreur (BAD REQUEST) avec les erreurs
        return $this->json([
            'errors' => $errorsString,
        ], Response::HTTP_BAD_REQUEST);
    }


    /**
     * Methode permettant la modification d'un utilisateur
     * 
     * @Route("/{id}", name="edit", methods={"PUT"})
     */
    public function edit(User $user, Request $request, UserPasswordHasherInterface $userPasswordHasherInterface)
    {
        // On restreint l'acces via le denyAccessUnlessGranted qui va aller chercher les informations situés dans le dossier src/Security/Voter, le voter concerné 'user-edit' et verifier si l'accès est autorisé
        $this->denyAccessUnlessGranted('USER_EDIT', $user);
        // on crée notre formulaire, auquel on associe 1: son type, 2: l'objet concerné, 3: on enleve la verification automatique du CSRF_protection 
        $form = $this->createForm(UserType::class, $user, ['csrf_protection' => false]);
        // on récupère le contenu de la requête qui est au format JSON
        // on décode $json pour transforme les données en tableau associatif
        $jsonArray = json_decode($request->getContent(), true);
        // On envoie notre tableau associatif à notre formulaire
        // La méthode submit va faire un peu comme handleRequest et prendre chacune
        // des clées du tableau pour les associer aux inputs du formulaire
        // Après cette étape, notre objet $user sera automatiquement rempli
        // Ça nous permet d'associer automatiquement/facilement des données reçues à notre objet $user
        // De plus, on profite du système de validations selon les contraintes dans nos champs de formulaire
        $form->submit($jsonArray);

        // On vérifie qu'il n'y a pas d'erreur dans le formulaire
        // Normalement on a pas besoin de $form->isSubmitted(), on est sur que le form est envoyé car
        // on a exécuté nous même la méthode submit()
        if ($form->isValid()) {
            // on récupére les données du champ password dans le formulaire
            $newPassword = $form->get('password')->getData();
            //si un mot de passe est récupéré
            if ($newPassword != null) {
                // on encode le mot de passe
                $encodePassword = $userPasswordHasherInterface->hashPassword($user, $newPassword);
                // On attribue à la propriété $password de notre User le mot de passe encodé
                $user->setPassword($encodePassword);
            }
            // on appelle l'entity manager qui permet de manipuler les données en BDD, puis on enregistre les modifications
            $this->getDoctrine()->getManager()->flush();
            // On est dans une API, on renvoit l'objet sérialisé pour confirmer son ajout
            // en précisant un code 200 OK
            return $this->json($user, Response::HTTP_OK, [], [
                'groups' => ['user_read'],
            ]);
        }

        // Si le formulaire n'est pas valide, on doit fournir une réponse avec les messages d'erreurs
        // Tous les messages d'erreurs sont dans $form->getErrors()
        // avec le booléen true, on précise qu'on veut la liste de toutes les erreurs de tous les champs du formulaire
        // Il est possible de parser notre ERROR en une string
        // on retourne le code erreur (BAD REQUEST) avec les erreurs
        return $this->json([
            'errors' => (string) $form->getErrors(true),
        ], Response::HTTP_BAD_REQUEST);
    }

    /**
     * Methode permettant de supprimer un utilisateur
     * 
     * @Route("/{id}", name="delete", methods={"DELETE"})
     */
    public function delete(User $user)
    {
        // On récupère l'EntityManager pour supprimer l'utilisateur
        $em = $this->getDoctrine()->getManager();
        // On supprime l'utilisateur
        $em->remove($user);
        // On enregistre en BDD les changements
        $em->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Methode permettant d'ajouter un avatar
     * 
     * @Route("/avatar/{id}", name="upload", methods={"POST"}, requirements={"id"="\d+"})
     */
    public function avatarUpload(Request $request, User $user, ImageUploader $imageUploader)
    {
        // on utilise le service créé (ImageUploader)
        // on demande à déplacer le fichier reçu et ça nous retourne le nouveau nom du fichier
        $newFilename = $imageUploader->upload($request, 'avatar');
        // on attribue à la propriété $avatar de notre User le nouveau nom random qu'on vient de déterminer
        $user->setAvatar($newFilename);

        // on appelle l'entity manager qui permet de manipuler les données en BDD
        $em = $this->getDoctrine()->getManager();
        // on persist le nouvel objet user, on le place en file d'attente avant enregistrement
        $em->persist($user);
        // on prend les objets modifiés (en file d'attente) et on les enregistre dans la BDD
        $em->flush();

        // On est dans une API, on renvoit l'objet sérialisé pour confirmer son ajout
        // en précisant un code 201 Created
        return $this->json($user, Response::HTTP_CREATED, [], [
            'groups' => ['user_read'],
        ]);
    }

}
