<?php

namespace App\Controller\Admin;

use App\Entity\Plants;
use App\Form\Admin\PlantsAdminType;
use App\Repository\PlantsRepository;
use App\Service\ImageUploaderAdmin;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/plants", name="admin_plants_")
 */
class PlantsController extends AbstractController
{
    /**
     * Méthode listant tous les fiches plantes
     * @Route("", name="browse")
     */
    public function browse(PlantsRepository $plantsRepository): Response // on 'typehint' PlantsRepository afin d'acceder aux méthodes de recherche comme findAll
    {
        // on retourne les objets dans notre vue TWIG via le render, en parametre 1 : le chemin dans le dossier template, parametre 2 : un tableau de données avec tous nos objets messages
        return $this->render('admin/plants/browse.html.twig', [
            'plants' => $plantsRepository->findAll(),
        ]);
    }

    /**
     * Méthode listant une fiche plante
     * On met en paramètre $plants qu'on typehint comme étant un objet de la classe Plants
     * Ça active le ParamConverter de Symfony qui va récupérer tout seul l'objet Message de la BDD
     * à partir de l'id fourni en URL
     * @Route("/{id}", name="read", requirements={"id"="\d+"})
     */
    public function read(Plants $plants): Response // on donne au param converter (entre parenthese), l'objet concerné, ici $plants. l'ID est récupéré directement dans le nom de la route
    {
        // on retourne à notre vue les informations de cette objet (1: chemin , 2: tableau des données de l'objet)
        return $this->render('admin/plants/read.html.twig', [
            'plants' => $plants,
        ]);
    }

    /**
     * Méthode permettant d'ajouter une fiche plante
     * @Route("/add", name="add")
     */
    public function add(Request $request, ImageUploaderAdmin $imageUploaderAdmin): Response
    {
        // on crée notre nouvel objet plants
        $plants = new Plants();
        // on crée notre formulaire, auquel on associe 1: son type, 2: l'objet concerné
        $form = $this->createForm(PlantsAdminType::class, $plants);
        // on asssocie le formulaire à la requete
        // handlerequest prend les données en Post et les places dans $form puis dans $plants
        $form->handleRequest($request);

        // on vérifie que le formulaire est bien soumis et valide
        // que les contraintes de validation sont respectées et que le token anti-CSRF est bon
        // si ce n'est pas le cas, il ne rentre pas dans les conditions du if
        if($form->isSubmitted() && $form->isValid()) {
            // On utilise le service créé (ImageUploader)
            // On demande à déplacer le fichier reçu et ça nous retourne le nouveau nom du fichier  
            $newFilename = $imageUploaderAdmin->upload($form, 'image');
            // On attribue à la propriété $image de notre Plants le nouveau nom random qu'on vient de déterminer
            $plants->setImage($newFilename);
            // on appelle l'entity manager qui permet de manipuler les données en BDD
            $em = $this->getDoctrine()->getManager();
            // on persist le nouvel objet plants, on le place en file d'attente avant enregistrement
            $em->persist($plants);
            // on prend les objets modifiés (en file d'attente) et on les enregistre dans la BDD
            $em->flush();
            // une fois l'objet enregistré, on redirige ver la page liste des fiches plantes
            return $this->redirectToRoute('admin_plants_browse');
        }
        // si les données du formulaire sont invalides et/ou non soumises, alors on affiche le formulaire
        return $this->render('admin/plants/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Méthode permettant la modification d'une fiche plante
     * @Route("/edit/{id}", name="edit", requirements={"id"="\d+"})
     */
    public function edit(Plants $plants, Request $request, ImageUploaderAdmin $imageUploaderAdmin): Response
    {
        $image = $plants->getImage();
        if (!is_null($image)) {
            $plants->setImage($image);
        }

        // on crée notre formulaire, auquel on associe 1: son type, 2: l'objet concerné 
        $form = $this->createForm(PlantsAdminType::class, $plants);
        // on asssocie le formulaire à la requete
        // handlerequest prend les données en Post et les places dans $form puis dans $messages
        $form->handleRequest($request);

        // on vérifie que le formulaire est bien soumis et valide
        // que les contraintes de validation sont respectées et que le token anti-CSRF est bon
        // si ce n'est pas le cas, il ne rentre pas dans les conditions du if
        if($form->isSubmitted() && $form->isValid()) {
            // si le champ image du formulaire  est différent de null
            if($form['image']->getData() !== null) {    
                // On utilise le service créé (ImageUploader)
                // On demande à déplacer le fichier reçu et ça nous retourne le nouveau nom du fichier  
                $newFilename = $imageUploaderAdmin->upload($form, 'image');
                // On remplace l'image existante par la nouvelle image uploadé
                $plants->setImage($newFilename);
            }
            else{
                // sinon on remplace l'image existante par $image récupéré précédemment (cf ligne 111 à 116)
                $plants->setImage($image);
            }
            
            // Comme l'objet existait en BDD, on n'a pas besoin de le persister
            // on appelle l'entity manager qui permet de manipuler les données en BDD et on enregistre (flush)
            $this->getDoctrine()->getManager()->flush();

            // on redirige vers la liste des fiches plantes
            return $this->redirectToRoute('admin_plants_browse');
        }
        // si les données du formulaire sont invalides et/ou non soumises, alors on affiche le formulaire
        return $this->render('admin/plants/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Méthode permettant de changer le status de l'annonce et non pas de supprimer l'annonce
     * @Route("/delete/{id}", name="delete", requirements={"id"="\d+"})
     */
    public function delete(Plants $plants, Request $request)
    {
        // on vérifie l'existence d'un token
        $token = $request->request->get('_token');

        // si le token est valide: comparaison entre le token généré et le token reçu de la requete
        if ($this->isCsrfTokenValid('deletePlants', $token)) {
            // ... alors on change le status de l'objet Ads
            $plants->setStatus('2');
            // on appelle l'entity manager qui permet de manipuler les données en BDD
            $em = $this->getDoctrine()->getManager();
            // on enregistre en BDD
            $em->flush();
            // on redirige sur la liste des fiche plantes
            return $this->redirectToRoute('admin_plants_browse');
        }

        // Si le token n'est pas valide, on lance une exception Access Denied
        throw $this->createAccessDeniedException('Le token n\'est pas valide.');
    }
}