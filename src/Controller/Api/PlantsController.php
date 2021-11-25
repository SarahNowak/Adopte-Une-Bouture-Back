<?php

namespace App\Controller\Api;

use App\Entity\Plants;
use App\Form\PlantsType;
use App\Repository\CategoryRepository;
use App\Repository\PlantsRepository;
use App\Service\ImageUploader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

 /**
  * Annotation (ligne 15 à 18) préfixant la route de toutes les méthodes du controleur
 * @Route("/api/plants", name="api_plants_")
 */
class PlantsController extends AbstractController
{

    /**
     * Method HTTP à préciser
     * 
     * @Route("", name="browse", methods={"GET"})
     */
    public function browse(PlantsRepository $plantsRepository): Response
    {
        // permet de recuperer la liste de toutes les plantes
        $plants = $plantsRepository->findAll();
        
        // on lui donne l'objet a serializer, le statut de reponse 200 ou (Response::HTTP_OK), un tableau vide correspondant aux entetes, le 4eme argument sont les elements de contextes pour lui dire comment serializer : selon quel group, ici avec plants_browse
        // on a mis dans entity Plants @Groups dans les annotations (pour ne pas avoir d'erreur de référence circulaire)
        // cela nous retourne les infos via insomnia
        // Attention a chaque ajout d'annotation faire un bin/console cache:clear
        return $this->json($plants, Response::HTTP_OK, [], [
            'groups' => ['plants_browse']
        ]);
    }

    /**
     * Methode listant une plante
     * 
     * @Route("/{id}", name="read", methods={"GET"}, requirements={"id"="\d+"})
     */
    public function read(Plants $plants) // on donne au param converter (entre parenthese), l'objet concerné, ici $plants. l'ID est récupéré directement dans le nom de la route
    {
        // on retourne au format JSON les informations de cette objet (1: objet , 2: réponse HTTP, 3: tableau des headers, 4: annotation de group @groups)
        return $this->json($plants, Response::HTTP_OK, [], [
            'groups' => ['plants_read']
        ]);
    }

    /**
     * Methode permettant d'ajouter une plante
     * 
     * @Route("", name="add", methods={"POST"})
     */
    public function add(Request $request, PlantsRepository $plantsRepository, CategoryRepository $categoryRepository)
    {
        // on crée notre nouvel objet Plants
        $plants = new Plants;
        
        // on crée notre formulaire, auquel on associe 1: son type, 2: l'objet concerné, 3: on enleve la verification automatique du CSRF_protection  
        $form = $this->createForm(PlantsType::class, $plants, ['csrf_protection' => false]);
        // on récupère le contenu de la requête qui est au format JSON
        $json = $request->getContent();
        // on décode $json pour transforme les données en tableau associatif        
        $jsonArray = json_decode($json, true);
        // On envoie notre tableau associatif à notre formulaire
        // La méthode submit va faire un peu comme handleRequest et prendre chacune
        // des clées du tableau pour les associer aux inputs du formulaire
        // Après cette étape, notre objet $plants sera automatiquement rempli
        // Ça nous permet d'associer automatiquement/facilement des données reçues à notre objet $plants
        // De plus, on profite du système de validations selon les contraintes dans nos champs de formulaire
        $form->submit($jsonArray);
        
        // On vérifie qu'il n'y a pas d'erreur dans le formulaire
        // Normalement on a pas besoin de $form->isSubmitted(), on est sur que le form est envoyé car
        // on a exécuté nous même la méthode submit()
        if ($form->isSubmitted() && $form->isValid()) {
            // on récupère l'ID de la category dans le formulaire soumis et valide, car nous avons une relation ManyToOne entre les entités Category et Plants
            $categoryId = $form->get('category')->getData();
            // on récupère toutes les informations de l'objet category correspondant à l'ID récupéré 
            $category = $categoryRepository->find($categoryId);
            // on remplace l'objet category existant dans plants par le nouvel objet category récupéré
            $plants->setCategory($category);

            // on appelle l'entity manager qui permet de manipuler les données en BDD
            $em = $this->getDoctrine()->getManager();
            // on persist le nouvel objet ads, on le place en file d'attente avant enregistrement
            $em->persist($plants);
            // on prend les objets modifiés (en file d'attente) et on les enregistre dans la BDD
            $em->flush();

            // On est dans une API, on renvoit l'objet sérialisé pour confirmer son ajout
            // en précisant un code 201 Created
            return $this->json($plants, Response::HTTP_CREATED, [], [
                'groups' => ['plants_read'],
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
     * Methode permettant de recuperer une image de plante
     * 
     * @Route("/image/{id}", name="upload", methods={"POST"}, requirements={"id"="\d+"})
     */
    public function imageUpload(Request $request, Plants $plants, ImageUploader $imageUploader)
    {
        // on utilise le service créé (ImageUploader)
        // on demande à déplacer le fichier reçu et ça nous retourne le nouveau nom du fichier  
        $newFilename = $imageUploader->upload($request, 'image');
        // on attribue à la propriété $image de notre Plants le nouveau nom random qu'on vient de déterminer
        $plants->setImage($newFilename);
        
        // on appelle l'entity manager qui permet de manipuler les données en BDD
        $em = $this->getDoctrine()->getManager();
        // on persist le nouvel objet user, on le place en file d'attente avant enregistrement
        $em->persist($plants);
        // on prend les objets modifiés (en file d'attente) et on les enregistre dans la BDD
        $em->flush();

        // On est dans une API, on renvoit l'objet sérialisé pour confirmer son ajout
        // en précisant un code 201 Created
        return $this->json($plants, Response::HTTP_CREATED, [], [
            'groups' => ['plants_read'],
        ]);
    }

    /**
     * Méthode permettant la modification d'une plante
     * 
     * @Route("/{id}", name="edit", methods={"PUT","PATCH", "POST"}, requirements={"id"="\d+"})
     */
    public function edit(Request $request, CategoryRepository $categoryRepository, Plants $plants, ImageUploader $imageUploader)
    {
        // on crée notre formulaire, auquel on associe 1: son type, 2: l'objet concerné, 3: on enleve la verification automatique du CSRF_protection  
        $form = $this->createForm(PlantsType::class, $plants, ['csrf_protection' => false]);
        // on récupère le contenu de la requête qui est au format JSON
        $json = $request->getContent();
        // on décode $json pour transforme les données en tableau associatif
        $jsonArray = json_decode($json, true);
        // On envoie notre tableau associatif à notre formulaire
        // La méthode submit va faire un peu comme handleRequest et prendre chacune
        // des clées du tableau pour les associer aux inputs du formulaire
        // Après cette étape, notre objet $plants sera automatiquement rempli
        // Ça nous permet d'associer automatiquement/facilement des données reçues à notre objet $plants
        // De plus, on profite du système de validations selon les contraintes dans nos champs de formulaire
        $form->submit($jsonArray);

        // On vérifie qu'il n'y a pas d'erreur dans le formulaire
        // Normalement on a pas besoin de $form->isSubmitted(), on est sur que le form est envoyé car
        // on a exécuté nous même la méthode submit()
        if ($form->isSubmitted() && $form->isValid()) {
            // on récupère l'ID de la category dans le formulaire soumis et valide, car nous avons une relation ManyToOne entre les entités Category et Plants
            $categoryId = $form->get('category')->getData();
            // on récupère toutes les informations de l'objet category correspondant à l'ID récupéré
            $category = $categoryRepository->find($categoryId);
            // on remplace l'objet category existant dans plants par le nouvel objet category récupéré
            $plants->setCategory($category);
            
            // on appelle l'entity manager qui permet de manipuler les données en BDD
            $em = $this->getDoctrine()->getManager();
            // on prend les objets modifiés (en file d'attente) et on les enregistre dans la BDD
            $em->flush();
            // On est dans une API, on renvoit l'objet sérialisé pour confirmer son ajout
            // en précisant un code 200 OK
            return $this->json($plants, Response::HTTP_OK, [], [
                'groups' => ['plants_read'],
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
     * Methode permettant de supprimer une plante
     * 
     * @Route("/{id}", name="delete", methods={"DELETE"}, requirements={"id"="\d+"})
     */
    public function delete(Plants $plants)
    {
        // On récupère l'EntityManager pour supprimer la plante
        $em = $this->getDoctrine()->getManager();
        // On supprime la plante
        $em->remove($plants);
        // On enregistre en BDD les changements
        $em->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

}
