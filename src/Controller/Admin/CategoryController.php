<?php

namespace App\Controller\Admin;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/category", name="admin_category_")
 */
class CategoryController extends AbstractController
{
    /**
     * Méthode listant toutes les catégories
     * @Route("", name="browse")
     */
    public function browse(CategoryRepository $categoryRepository): Response // on 'typehint' CategoryRepository afin d'acceder aux méthodes de recherche comme findAll
    {
        // on retourne les objets dans notre vue TWIG via le render, en parametre 1 : le chemin dans le dossier template, parametre 2 : un tableau de données avec tous nos objets category
        return $this->render('admin/category/browse.html.twig', [
            'categories' => $categoryRepository->findAll(),
        ]);
    }

    /**
     * Méthode listant une catégorie
     * On met en paramètre $category qu'on typehint comme étant un objet de la classe Category
     * Ça active le ParamConverter de Symfony qui va récupérer tout seul l'objet Category de la BDD
     * à partir de l'id fourni en URL
     * @Route("/{id}", name="read", requirements={"id"="\d+"})
     */
    public function read(Category $category): Response // on donne au param converter (entre parenthese), l'objet concerné, ici $category. l'ID est récupéré directement dans le nom de la route
    {
        // on retourne à notre vue les informations de cette objet (1: chemin , 2: tableau des données de l'objet)
        return $this->render('admin/category/read.html.twig', [
            'category' => $category,
        ]);
    }

    /**
     * Méthode permettant d'ajouter une catégorie
     * @Route("/add", name="add")
     */
    public function add(Request $request)
    {
        // on crée notre nouvel objet category
        $category = new Category;

        // on crée notre formulaire, auquel on associe 1: son type, 2: l'objet concerné
        $form = $this->createForm(CategoryType::class, $category);

        // on asssocie le formulaire à la requete
        // handlerequest prend les données en Post et les places dans $form puis dans $category
        $form->handleRequest($request);

        // on vérifie que le formulaire est bien soumis et valide
        // que les contraintes de validation sont respectées et que le token anti-CSRF est bon
        // si ce n'est pas le cas, il ne rentre pas dans les conditions du if
        if ($form->isSubmitted() && $form->isValid()) {

            // on appelle l'entity manager qui permet de manipuler les données en BDD
            $em = $this->getDoctrine()->getManager();
            // on persist le nouvel objet category, on le place en file d'attente avant enregistrement
            $em->persist($category);
            // on prend les objets modifiés (en file d'attente) et on les enregistre dans la BDD
            $em->flush();

            // une fois l'objet enregistré, on redirige ver la page liste des annonces
            return $this->redirectToRoute('admin_category_browse');
        }

        // si les données du formulaire sont invalides et/ou non soumises, alors on affiche le formulaire
        return $this->render ('admin/category/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Méthode permettant la modification d'une catégorie
     * @Route("/edit/{id}", name="edit", requirements={"id"="\d+"})
     */
    public function edit(Request $request, Category $category)
    {
        // on crée notre formulaire, auquel on associe 1: son type, 2: l'objet concerné 
        $form = $this->createForm(CategoryType::class, $category);
        // on asssocie le formulaire à la requete
        // handlerequest prend les données en Post et les places dans $form puis dans $category
        $form->handleRequest($request);

        // on vérifie que le formulaire est bien soumis et valide
        // que les contraintes de validation sont respectées et que le token anti-CSRF est bon
        // si ce n'est pas le cas, il ne rentre pas dans les conditions du if
        if ($form->isSubmitted() && $form->isValid()) {
            // Comme l'objet existait en BDD, on n'a pas besoin de le persister
            // on appelle l'entity manager qui permet de manipuler les données en BDD et on enregistre (flush)
            $em = $this->getDoctrine()->getManager();

            $em->flush();
            // on redirige vers la liste des catégorie
            return $this->redirectToRoute('admin_category_browse');
        }
        // si les données du formulaire sont invalides et/ou non soumises, alors on affiche le formulaire
        return $this->render ('admin/category/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Méthode permettant de changer le status de la categorie et non pas de supprimer la categorie
     * @Route("/delete/{id}", name="delete", requirements={"id"="\d+"})
     */
    public function delete(Category $category, Request $request)
    {
        // on vérifie l'existence d'un token
        $token = $request->request->get('_token');
        
        // si le token est valide: comparaison entre le token généré et le token reçu de la requete
        if ($this->isCsrfTokenValid('deleteCategory', $token)) {
            // ... alors on change le status de l'objet Category
            $category->setStatus('2');
            // on appelle l'entity manager qui permet de manipuler les données en BDD
            $em = $this->getDoctrine()->getManager();
            // on enregistre en BDD
            $em->flush();
            // on redirige sur la liste des categorie
            return $this->redirectToRoute('admin_category_browse');
        }

        // Si le token n'est pas valide, on lance une exception Access Denied
        throw $this->createAccessDeniedException('Le token n\'est pas valide.');
    }
}