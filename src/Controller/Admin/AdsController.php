<?php

namespace App\Controller\Admin;

use App\Entity\Ads;
use App\Form\Admin\AdminAdsType;
use App\Repository\AdsRepository;
use App\Repository\CategoryRepository;
use App\Service\ImageUploaderAdmin;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * Annotation (ligne 17 à 21) préfixant la route de toutes les méthodes du controleur
 * 
 * @Route("/admin/ads", name="admin_ads_")
 */
class AdsController extends AbstractController
{
    /**
     * Méthode listant toutes les annonces
     * 
     * @Route("", name="browse")
     */
    public function browse(AdsRepository $adsRepository): Response // on 'typehint' AdsRepository afin d'acceder aux méthodes de recherche comme findAll
    {
        // on stock dans notre variable $ads tous les objets récupérés via la methodes findAll de AdsRepository
        $ads = $adsRepository->findAll();

        // on retourne les objets dans notre vue TWIG via le render, en parametre 1 : le chemin dans le dossier template, parametre 2 : un tableau de données avec tous nos objets $ads
        return $this->render('admin/ads/browse.html.twig', [
            'ads' => $ads,
        ]);
    }

    /**
    * Méthode listant une annonce
    * On met en paramètre $ads qu'on typehint comme étant un objet de la classe Ads
    * Ça active le ParamConverter de Symfony qui va récupérer tout seul l'objet Ads de la BDD
    * à partir de l'id fourni en URL
    * @Route("/{id}", name="read", requirements={"id"="\d+"})
    */
    public function read(Ads $ads): Response // on donne au param converter (entre parenthese), l'objet concerné, ici $ads. l'ID est récupéré directement dans le nom de la route
    {
        // on retourne à notre vue les informations de cette objet (1: chemin , 2: tableau des données de l'objet)
        return $this->render('admin/ads/read.html.twig', [
            'ads' => $ads,
        ]);
    }

    /**
     * Méthode permettant d'ajouter une annonce
     * 
     * @Route("/add", name="add")
     */
    public function add(Request $request, CategoryRepository $categoryRepository, ImageUploaderAdmin $imageUploaderAdmin)
    {
        // on crée notre nouvel objet ads
        $ads = new Ads;

        // on crée notre formulaire, auquel on associe 1: son type, 2: l'objet concerné
        $form = $this->createForm(AdminAdsType::class, $ads);

        // on asssocie le formulaire à la requete
        // handlerequest prend les données en Post et les places dans $form puis dans $ads
        $form->handleRequest($request);

        // on vérifie que le formulaire est bien soumis et valide
        // que les contraintes de validation sont respectées et que le token anti-CSRF est bon
        // si ce n'est pas le cas, il ne rentre pas dans les conditions du if
        if ($form->isSubmitted() && $form->isValid()) {

            // on récupère l'ID de la category dans le formulaire soumis et valide, car nous avons une relation ManyToOne entre les entités Category et Ads
            $categoryId = $form->get('category')->getData();
            // on récupère toutes les informations de l'objet category correspondant à l'ID récupéré 
            $category = $categoryRepository->find($categoryId);          
            // on remplace l'objet category existant dans ads par le nouvel objet category récupéré
            $ads->setCategory($category);

            // on appelle le service créé (cf dossier Service), afin d'appeler la méthode upload
            $newFilename = $imageUploaderAdmin->upload($form, 'image');            
            // on remplace l'image existante par la nouvelle image uploadé
            $ads->setImage($newFilename);

            // on appelle l'entity manager qui permet de manipuler les données en BDD
            $em = $this->getDoctrine()->getManager();
            // on persist le nouvel objet ads, on le place en file d'attente avant enregistrement
            $em->persist($ads);
            // on prend les objets modifiés (en file d'attente) et on les enregistre dans la BDD
            $em->flush();

            // une fois l'objet enregistré, on redirige ver la page liste des annonces
            return $this->redirectToRoute('admin_ads_browse');
        }

        // si les données du formulaire sont invalides et/ou non soumises, alors on affiche le formulaire
        return $this->render ('admin/ads/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Méthode permettant la modification d'une annonce
     * 
     * @Route("/edit/{id}", name="edit", requirements={"id"="\d+"})
     */
    public function edit(Ads $ads, CategoryRepository $categoryRepository, ImageUploaderAdmin $imageUploaderAdmin, Request $request)
    {
        // on récupère la propriété image de l'objet ads (le nom de l'image)
        $image = $ads->getImage();
        // si $image n'est pas NULL...
        if (!is_null($image)){
            // ...alors on enregistre $image dans la propriété image de l'objet Ads
            $ads->setImage($image);
        }
        // on crée notre formulaire, auquel on associe 1: son type, 2: l'objet concerné 
        $form = $this->createForm(AdminAdsType::class, $ads);
        // on asssocie le formulaire à la requete
        // handlerequest prend les données en Post et les places dans $form puis dans $ads
        $form->handleRequest($request);
        // on vérifie que le formulaire est bien soumis et valide
        // que les contraintes de validation sont respectées et que le token anti-CSRF est bon
        // si ce n'est pas le cas, il ne rentre pas dans les conditions du if
        if ($form->isSubmitted() && $form->isValid()) {

            // on récupère l'ID de la category dans le formulaire soumis et valide, car nous avons une relation ManyToOne entre les entités Category et Ads
            $categoryId = $form->get('category')->getData();
            // on récupère toutes les informations de l'objet category correspondant à l'ID récupéré 
            $category = $categoryRepository->find($categoryId);          
            // on remplace l'objet category existant dans ads par le nouvel objet category récupéré
            $ads->setCategory($category);

            // si la donnée du champ image récupérée dans le formulaire n'est pas NULL...
            if($form['image']->getData() !== null) {  
                //... alors on appelle le service créé (cf dossier Service), afin d'appeler la méthode upload               
                $newFilename = $imageUploaderAdmin->upload($form, 'image');
                // on remplace l'image existante par la nouvelle image uploadé
                $ads->setImage($newFilename);
            }
            // sinon
            else{
                // on remplace l'image existante par $image récupéré précédemment (cf ligne 111 à 116)
                $ads->setImage($image);
            }

            // Comme l'objet existait en BDD, on n'a pas besoin de le persister
            // on appelle l'entity manager qui permet de manipuler les données en BDD et on enregistre (flush)
            $this->getDoctrine()->getManager()->flush();
            
            // on redirige vers la liste des annonces
            return $this->redirectToRoute('admin_ads_browse');
        }
        // si les données du formulaire sont invalides et/ou non soumises, alors on affiche le formulaire
        return $this->render ('admin/ads/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Méthode permettant de changer le status de l'annonce et non pas de supprimer l'annonce
     * 
     * @Route("/delete/{id}", name="delete", requirements={"id"="\d+"}, methods={"DELETE","POST"})
     */
    public function delete(Request $request, Ads $ads)
    {
       // on vérifie l'existence d'un token
       $token = $request->request->get('_token');

       // si le token est valide: comparaison entre le token généré et le token reçu de la requete
       if ($this->isCsrfTokenValid('deleteAds', $token)) {
           // ... alors on change le status de l'objet Ads
           $ads->setStatus(2);
           // on appelle l'entity manager qui permet de manipuler les données en BDD
           $em = $this->getDoctrine()->getManager();
           // on enregistre en BDD
           $em->flush();
           
           // on redirige sur la liste des annonces
           return $this->redirectToRoute('admin_ads_browse');
       }

       // ... si le token n'est pas valide, on leve une exception
       throw $this->createAccessDeniedException('Le token n\'est pas valide.');
    }
   
}
