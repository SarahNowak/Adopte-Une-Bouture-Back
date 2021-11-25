<?php

namespace App\Controller\Api;

use App\Entity\Ads;
use App\Repository\CategoryRepository;
use App\Entity\Category;
use App\Entity\User;
use App\Form\AdsImageType;
use App\Form\AdsStatusType;
use App\Form\AdsType;
use App\Repository\AdsRepository;
use App\Service\ImageUploader;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Mime\Header\Headers;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\VarExporter\Internal\Values;


/**
 * Annotation (ligne 26 à 30) préfixant la route de toutes les méthodes du controleur
 * 
 * @Route("/api/ads", name="api_ads_")
 */
class AdsController extends AbstractController
{
    /**
     * Method HTTP à préciser
     * 
     * @Route("", name="browse", methods={"GET"})
     */
    public function browse(AdsRepository $adsRepository): Response
    {
        // permet de recuperer les annonces ayant le status 1 (active) dans l'ordre de création descendant
        $ads = $adsRepository->findBy(['status' => 1], ['createdAt' => 'DESC']);
        
        // on lui donne l'objet a serializer, le statut de reponse 200 ou (Response::HTTP_OK), un tableau vide correspondant aux entetes, le 4eme argument sont les elements de contextes pour lui dire comment serializer : selon quel group, ici avec ads_browse
        // on a mis dans entity Ads @Groups dans les annotations (pour ne pas avoir d'erreur de référence circulaire)
        // cela nous retourne les infos via insomnia
        // Attention a chaque ajout d'annotation faire un bin/console cache:clear
        return $this->json($ads, Response::HTTP_OK, [], [
            'groups' => ['ads_browse']
        ]);
    }

    /**
     * On met en paramètre $ads qu'on typehint comme étant un objet de la classe Ads
     * Ça active le ParamConverter de Symfony qui va récupérer tout seul l'objet Ads de la BDD
     * à partir de l'id fourni en URL
     * Méthode listant une annonce
     * 
     * @Route("/{id}", name="read", methods={"GET"}, requirements={"id"="\d+"})
     */
    public function read(Ads $ads) // on donne au param converter (entre parenthese), l'objet concerné, ici $ads. l'ID est récupéré directement dans le nom de la route
    {
        // on retourne au format JSON les informations de cette objet (1: objet , 2: réponse HTTP, 3: tableau des headers, 4: annotation de group @groups)
        return $this->json($ads, Response::HTTP_OK, [], [
            'groups' => ['ads_read']
        ]);
    }

    /**
     * Methode permettant d'ajouter une annonce
     * 
     * @Route("", name="add", methods={"POST"})
     */
    public function add(Request $request, CategoryRepository $categoryRepository)
    {
        // on crée notre nouvel objet ads
        $ads = new Ads;
        // on crée notre formulaire, auquel on associe 1: son type, 2: l'objet concerné, 3: on enleve la verification automatique du CSRF_protection   
        $form = $this->createForm(AdsType::class, $ads, ['csrf_protection' => false]);
        // on récupère le contenu de la requête qui est au format JSON
        $json = $request->getContent();
        // on décode $json pour transforme les données en tableau associatif
        $jsonArray = json_decode($json, true);
        // On envoie notre tableau associatif à notre formulaire
        // La méthode submit va faire un peu comme handleRequest et prendre chacune
        // des clées du tableau pour les associer aux inputs du formulaire
        // Après cette étape, notre objet $ads sera automatiquement rempli
        // Ça nous permet d'associer automatiquement/facilement des données reçues à notre objet $ads
        // De plus, on profite du système de validations selon les contraintes dans nos champs de formulaire
        $form->submit($jsonArray);
        
        // On vérifie qu'il n'y a pas d'erreur dans le formulaire
        // Normalement on a pas besoin de $form->isSubmitted(), on est sur que le form est envoyé car
        // on a exécuté nous même la méthode submit()
        if ($form->isSubmitted() && $form->isValid()) {
            // on récupère l'ID de la category dans le formulaire soumis et valide, car nous avons une relation ManyToOne entre les entités Category et Ads
            $categoryId = $form->get('category')->getData();
            // on récupère toutes les informations de l'objet category correspondant à l'ID récupéré 
            $category = $categoryRepository->find($categoryId);
            // on remplace l'objet category existant dans ads par le nouvel objet category récupéré
            $ads->setCategory($category);

            // on appelle l'entity manager qui permet de manipuler les données en BDD
            $em = $this->getDoctrine()->getManager();
            // on persist le nouvel objet ads, on le place en file d'attente avant enregistrement
            $em->persist($ads);
            // on prend les objets modifiés (en file d'attente) et on les enregistre dans la BDD
            $em->flush();

            // On est dans une API, on renvoit l'objet sérialisé pour confirmer son ajout
            // en précisant un code 201 Created
            return $this->json($ads, Response::HTTP_CREATED, [], [
                'groups' => ['ads_read'],
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
     * Méthode permettant d'uploadé une image
     * 
     * @Route("/image/{id}", name="upload", methods={"POST"}, requirements={"id"="\d+"})
     */
    public function imageUpload(Request $request, Ads $ads, ImageUploader $imageUploader)
    {
            // on appelle le service créé (cf dossier Service), afin d'appeler la méthode upload
            $newFilename = $imageUploader->upload($request, 'image');
            // on remplace l'image existante par la nouvelle image uploadé
            $ads->setImage($newFilename);
            
            // on appelle l'entity manager qui permet de manipuler les données en BDD
            $em = $this->getDoctrine()->getManager();
            // on persist le nouvel objet ads, on le place en file d'attente avant enregistrement
            $em->persist($ads);
            // on prend les objets modifiés (en file d'attente) et on les enregistre dans la BDD
            $em->flush();

            // On est dans une API, on renvoit l'objet sérialisé pour confirmer son ajout
            // en précisant un code 201 Created
            return $this->json($ads, Response::HTTP_CREATED, [], [
                'groups' => ['ads_read'],
            ]);
    }

    /**
     * Méthode permettant la modification d'une annonce
     * 
     * @Route("/{id}", name="edit", methods={"PUT","PATCH", "POST"}, requirements={"id"="\d+"})
     */
    public function edit(Request $request, CategoryRepository $categoryRepository, Ads $ads, ImageUploader $imageUploader)
    {
        // on crée notre formulaire, auquel on associe 1: son type, 2: l'objet concerné, 3: on enleve la verification automatique du CSRF_protection  
        $form = $this->createForm(AdsType::class, $ads, ['csrf_protection' => false]);
        // on récupère le contenu de la requête qui est au format JSON
        $json = $request->getContent();
        // on décode $json pour transforme les données en tableau associatif
        $jsonArray = json_decode($json, true);
        // On envoie notre tableau associatif à notre formulaire
        // La méthode submit va faire un peu comme handleRequest et prendre chacune
        // des clées du tableau pour les associer aux inputs du formulaire
        // Après cette étape, notre objet $ads sera automatiquement rempli
        // Ça nous permet d'associer automatiquement/facilement des données reçues à notre objet $ads
        // De plus, on profite du système de validations selon les contraintes dans nos champs de formulaire
        $form->submit($jsonArray);

        // On vérifie qu'il n'y a pas d'erreur dans le formulaire
        // Normalement on a pas besoin de $form->isSubmitted(), on est sur que le form est envoyé car
        // on a exécuté nous même la méthode submit()
        if ($form->isSubmitted() && $form->isValid()) {
            // on récupère l'ID de la category dans le formulaire soumis et valide, car nous avons une relation ManyToOne entre les entités Category et Ads
            $categoryId = $form->get('category')->getData();
            // on récupère toutes les informations de l'objet category correspondant à l'ID récupéré 
            $category = $categoryRepository->find($categoryId);
            // on remplace l'objet category existant dans ads par le nouvel objet category récupéré
            $ads->setCategory($category);
            
            // on appelle l'entity manager qui permet de manipuler les données en BDD
            $em = $this->getDoctrine()->getManager();
            // on prend les objets modifiés (en file d'attente) et on les enregistre dans la BDD
            $em->flush();

            // On est dans une API, on renvoit l'objet sérialisé pour confirmer son ajout
            // en précisant un code 200 OK
            return $this->json($ads, Response::HTTP_OK, [], [
                'groups' => ['ads_read'],
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
     * Methode permettant de supprimer une annonce
     * 
     * @Route("/{id}", name="delete", methods={"DELETE"}, requirements={"id"="\d+"})
     */
    public function delete(Ads $ads)
    {
            // On récupère l'EntityManager pour supprimer l'annonce
            $em = $this->getDoctrine()->getManager();
            // On supprime l'annonce
            $em->remove($ads);
            // On enregistre en BDD les changements
            $em->flush();

            return $this->json(null, Response::HTTP_NO_CONTENT);
    }
        
    /**
     * Methode permettant d'ajouter une annonce en favoris
     * 
     * @Route("/favoris/{id}", name="add_favoris", methods={"POST"}, requirements={"id"="\d+"})
     */
    public function addFavoris(Ads $ads)
    {
        // si il n'y a pas d'annonce ...
        if(!$ads){
            // ... on lève une exception
            throw new NotFoundHttpException('Pas d\'annonce trouvée');
        }
        // via la methode addFavori, créée dans l'entité ads (car il existe une relation ManyToMany) on va lui indiqué l'utilisateur actuel
        $ads->addFavori($this->getUser());

        // on appelle l'entity manager qui permet de manipuler les données en BDD
        $em = $this->getDoctrine()->getManager();
        // on persist le nouvel objet ads, on le place en file d'attente avant enregistrement
        $em->persist($ads);
        // on prend les objets modifiés (en file d'attente) et on les enregistre dans la BDD
        $em->flush();

        // on renvoit l'objet sérialisé pour confirmer son ajout en précisant un code 201 Created
        return $this->json($ads, Response::HTTP_CREATED, [], [
            'groups' => ['user_favoris'],
        ]);
    }

    /**
     * @Route("/favoris/{id}", name="delete_favoris", methods={"DELETE"}, requirements={"id"="\d+"})
     */
    public function deleteFavoris(Ads $ads)
    {
        // si il n'y a pas d'annonce ...
        if(!$ads){
            // ... on lève une exception
            throw new NotFoundHttpException('Pas d\'annonce trouvée');
        }
        // via la methode removeFavori, créée dans l'entité ads (car il existe une relation ManyToMany) on va lui indiqué l'utilisateur actuel
        $ads->removeFavori($this->getUser());
        // on appelle l'entity manager qui permet de manipuler les données en BDD
        $em = $this->getDoctrine()->getManager();
        // on persist le nouvel objet ads, on le place en file d'attente avant enregistrement
        $em->persist($ads);
        // on prend les objets modifiés (en file d'attente) et on les enregistre dans la BDD
        $em->flush();

        // on renvoit rien juste une reponse comme quoi il n'ya plus de contenu
        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Méthode permet à l'utilisateur de changer le statut de son annonce
     * 
     * @Route("/status/{id}", name = "status", methods={"PATCH"})
     */
    public function status(Ads $ads, Request $request)
    {
        // on crée notre formulaire, auquel on associe 1: son type, 2: l'objet concerné, 3: on enleve la verification automatique du CSRF_protection 
        $form = $this->createForm(AdsStatusType::class, $ads, ['csrf_protection' => false]);
        // on récupère le contenu de la requête qui est au format JSON
        $json = $request->getContent();
        // on décode $json pour transforme les données en tableau associatif
        $jsonArray = json_decode($json, true);
        // On envoie notre tableau associatif à notre formulaire
        // La méthode submit va faire un peu comme handleRequest et prendre chacune
        // des clées du tableau pour les associer aux inputs du formulaire
        // Après cette étape, notre objet $ads sera automatiquement rempli
        // Ça nous permet d'associer automatiquement/facilement des données reçues à notre objet $ads
        // De plus, on profite du système de validations selon les contraintes dans nos champs de formulaire
        $form->submit($jsonArray);

        // On vérifie qu'il n'y a pas d'erreur dans le formulaire
        // Normalement on a pas besoin de $form->isSubmitted(), on est sur que le form est envoyé car
        // on a exécuté nous même la méthode submit()
        if ($form->isSubmitted() && $form->isValid()) {
            // on récupère l'ID du status dans le formulaire 
            $status = $form->get('status')->getData();
            // on remplace le statut de l'objet existant par le nouvel objet status récupéré
            $ads->setStatus($status);
            // on appelle l'entity manager qui permet de manipuler les données en BDD
            $em = $this->getDoctrine()->getManager();
            // on prend les objets modifiés (en file d'attente) et on les enregistre dans la BDD
            $em->flush();

            // On est dans une API, on renvoit l'objet sérialisé pour confirmer son ajout
            // en précisant un code 201 Created
            return $this->json($ads, Response::HTTP_OK, [], [
                'groups' => ['ads_read'],
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




