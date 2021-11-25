<?php

namespace App\Controller\Api;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Annotation (ligne 11 à 15) préfixant la route de toutes les méthodes du controleur
 * 
 * @Route("/api/category", name="api_category")
 */
class CategoryController extends AbstractController
{
    
    /**
     * Method HTTP à préciser
     * 
     * @Route("", name="browse", methods={"GET"})
     */
    public function browse(CategoryRepository $categoryRepository): Response
    {
        // permet de recuperer la liste de toutes les categories
        $categories = $categoryRepository->findAll();
        
        // on lui donne l'objet a serializer, le statut de reponse 200 ou (Response::HTTP_OK), un tableau vide correspondant aux entetes, le 4eme argument sont les elements de contextes pour lui dire comment serializer : selon quel group, ici avec category_browse
        // on a mis dans entity Category @Groups dans les annotations (pour ne pas avoir d'erreur de référence circulaire)
        // cela nous retourne les infos via insomnia
        // Attention a chaque ajout d'annotation faire un bin/console cache:clear
        return $this->json($categories, Response::HTTP_OK, [], [
            'groups' => ['category_browse']
        ]);
    }

    /**
     * Méthode listant une categorie
     * 
     * @Route("/{id}", name="read", methods={"GET"}, requirements={"id"="\d+"})
     */
    public function read(Category $category) // on donne au param converter (entre parenthese), l'objet concerné, ici $category. l'ID est récupéré directement dans le nom de la route
    {
        // on retourne au format JSON les informations de cette objet (1: objet , 2: réponse HTTP, 3: tableau des headers, 4: annotation de group @groups)
        return $this->json($category, Response::HTTP_OK, [], [
            'groups' => ['category_read']
        ]);
    }
}
