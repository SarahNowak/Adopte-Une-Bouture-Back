<?php

namespace App\Controller\Api;

use App\Entity\Messages;
use App\Form\MessagesType;
use App\Repository\MessagesRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Message;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Annotation (ligne 14 à 17) préfixant la route de toutes les méthodes du controleur
 * 
 * @Route("/api/messages", name="api_messages_")
 */
class MessagesController extends AbstractController
{
    /**
     * Method HTTP à préciser
     * 
     * @Route("", name="browse", methods="GET")
     */
    public function browse(MessagesRepository $messagesRepository): Response
    {
        // permet de recuperer la liste de tous les messages
        $messages = $messagesRepository->findAll();

        // on lui donne l'objet a serializer, le statut de reponse 200 ou (Response::HTTP_OK), un tableau vide correspondant aux entetes, le 4eme argument sont les elements de contextes pour lui dire comment serializer : selon quel group, ici avec message_browse
        // on a mis dans entity Message @Groups dans les annotations (pour ne pas avoir d'erreur de référence circulaire)
        // cela nous retourne les infos via insomnia
        // Attention a chaque ajout d'annotation faire un bin/console cache:clear
        return $this->json($messages, Response::HTTP_OK , [], [
            'groups' => ['messages_browse'],
        ]);
    }

    /**
     * Methode listant un message
     * 
     * @Route("/{id}", name="read", methods="GET", requirements={"id"="\d+"})
     */
    public function read(Messages $messages): Response // on donne au param converter (entre parenthese), l'objet concerné, ici $messages. l'ID est récupéré directement dans le nom de la route
    {
        // on retourne au format JSON les informations de cette objet (1: objet , 2: réponse HTTP, 3: tableau des headers, 4: annotation de group @groups)
        return $this->json($messages, Response::HTTP_OK , [], [
            'groups' => ['messages_read'],
        ]);
    }

    /**
     * Methode permettant d'ajouter un message
     * 
     * @Route("", name="add", methods="POST")
     */
    public function add(Request $request): Response
    {
        // on crée notre nouvel objet messages
        $messages = new Messages;

        // on crée notre formulaire, auquel on associe 1: son type, 2: l'objet concerné, 3: on enleve la verification automatique du CSRF_protection  
        $form = $this->createForm(MessagesType::class, $messages , ['csrf_protection' => false]);
        // on récupère le contenu de la requête qui est au format JSON
        $json = $request->getContent();
        // on décode $json pour transforme les données en tableau associatif       
        $jsonArray = json_decode($json, true);
        // On envoie notre tableau associatif à notre formulaire
        // La méthode submit va faire un peu comme handleRequest et prendre chacune
        // des clées du tableau pour les associer aux inputs du formulaire
        // Après cette étape, notre objet $messages sera automatiquement rempli
        // Ça nous permet d'associer automatiquement/facilement des données reçues à notre objet $messages
        // De plus, on profite du système de validations selon les contraintes dans nos champs de formulaire
        $form->submit($jsonArray);

        // On vérifie qu'il n'y a pas d'erreur dans le formulaire
        // Normalement on a pas besoin de $form->isSubmitted(), on est sur que le form est envoyé car
        // on a exécuté nous même la méthode submit()
        if ($form->isSubmitted() && $form->isValid()){
            // on appelle l'entity manager qui permet de manipuler les données en BDD
            $em = $this->getDoctrine()->getManager();
            // on persist le nouvel objet ads, on le place en file d'attente avant enregistrement
            $em->persist($messages);
            // on prend les objets modifiés (en file d'attente) et on les enregistre dans la BDD
            $em->flush();
            
            // On est dans une API, on renvoit l'objet sérialisé pour confirmer son ajout
            // en précisant un code 201 Created
            return $this->json($messages, Response::HTTP_CREATED , [], [
                'groups' => ['messages_read'],
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
}
