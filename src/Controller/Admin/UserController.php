<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Form\Admin\UserAdminType;
use App\Repository\UserRepository;
use App\Service\ImageUploader;
use App\Service\ImageUploaderAdmin;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * @Route("/admin/user", name="admin_user_")
 */
class UserController extends AbstractController
{
    /**
     * Méthode listant tous les utilisateurs
     * @Route("", name="browse")
     */
    public function browse(UserRepository $userRepository): Response // on 'typehint' UserRepository afin d'acceder aux méthodes de recherche comme findAll
    {
         // on retourne les objets dans notre vue TWIG via le render, en parametre 1 : le chemin dans le dossier template, parametre 2 : un tableau de données avec tous nos objets messages
        return $this->render('admin/user/browse.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    /**
     * Méthode listant un utilisateur
     * On met en paramètre $user qu'on typehint comme étant un objet de la classe User
     * Ça active le ParamConverter de Symfony qui va récupérer tout seul l'objet Message de la BDD
     * à partir de l'id fourni en URL
     * @Route("/{id}", name="read", requirements={"id"="\d+"})
     */
    public function read(User $user): Response // on donne au param converter (entre parenthese), l'objet concerné, ici $user. l'ID est récupéré directement dans le nom de la route
    {
        // on retourne à notre vue les informations de cette objet (1: chemin , 2: tableau des données de l'objet)
        return $this->render('admin/user/read.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * Méthode permettant d'ajouter un utilisateur
     * @Route("/add", name="add")
     */
    public function add(Request $request, UserPasswordHasherInterface $userPasswordHasherInterface, ImageUploaderAdmin $imageUploaderAdmin): Response
    {
        // on crée notre nouvel objet user
        $user = new User();
        // on crée notre formulaire, auquel on associe 1: son type, 2: l'objet concerné
        $form = $this->createForm(UserAdminType::class, $user);
        // on asssocie le formulaire à la requete
        // handlerequest prend les données en Post et les places dans $form puis dans $user
        $form->handleRequest($request);
        
        // on vérifie que le formulaire est bien soumis et valide
        // que les contraintes de validation sont respectées et que le token anti-CSRF est bon
        // si ce n'est pas le cas, il ne rentre pas dans les conditions du if
        if($form->isSubmitted() && $form->isValid()) {

            // on récupére les données du champ password dans le formulaire
            $newPassword = $form->get('password')->getData();
            //si un mot de passe est récupéré
            if ($newPassword != null) {
                // on encode le mot de passe
                $encodePassword = $userPasswordHasherInterface->hashPassword($user, $newPassword);
                // On attribue à la propriété $password de notre User le mot de passe encodé
                $user->setPassword($encodePassword);
            }

            // on utilise le service créé (ImageUploader)
            // on demande à déplacer le fichier reçu et ça nous retourne le nouveau nom du fichier  
            $newFilename = $imageUploaderAdmin->upload($form, 'avatar');
            // on attribue à la propriété $avatar de notre User le nouveau nom random qu'on vient de déterminer
            $user->setAvatar($newFilename);
            // on appelle l'entity manager qui permet de manipuler les données en BDD
            $em = $this->getDoctrine()->getManager();
            // on persist le nouvel objet user, on le place en file d'attente avant enregistrement
            $em->persist($user);
            // on prend les objets modifiés (en file d'attente) et on les enregistre dans la BDD
            $em->flush();
            // une fois l'objet enregistré, on redirige ver la page liste des fiches utilisateurs
            return $this->redirectToRoute('admin_user_browse');
        }
        // si les données du formulaire sont invalides et/ou non soumises, alors on affiche le formulaire
        return $this->render('admin/user/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Méthode permettant la modification d'un utilisateur
     * @Route("/edit/{id}", name="edit", requirements={"id"="\d+"})
     */
    public function edit(User $user, Request $request, ImageUploaderAdmin $imageUploaderAdmin): Response
    {
        // on récupére l'avatar de l'utlisateur
        $avatar = $user->getAvatar();
        // si l'utilisateur a déjà un avatar d'enregistré
        if (!is_null($avatar)) {
            // on attribue à la propriété $avatar de notre User l'avatar récupéré
            $user->setAvatar($avatar);
        }

        // on crée notre formulaire, auquel on associe 1: son type, 2: l'objet concerné 
        $form = $this->createForm(UserAdminType::class, $user);
        // on asssocie le formulaire à la requete
        // handlerequest prend les données en Post et les places dans $form puis dans $messages
        $form->handleRequest($request);

        // on vérifie que le formulaire est bien soumis et valide
        // que les contraintes de validation sont respectées et que le token anti-CSRF est bon
        // si ce n'est pas le cas, il ne rentre pas dans les conditions du if
        if($form->isSubmitted() && $form->isValid()) {
          
            // on récupére les données du champ avatar dans le formulaire
            // si c'est différent de null
            if($form['avatar']->getData() !== null) {   
                // on utilise le service créé (ImageUploader)
                // on demande à déplacer le fichier reçu et ça nous retourne le nouveau nom du fichier             
                $newFilename = $imageUploaderAdmin->upload($form, 'avatar');
                // on attribue à la propriété $avatar de notre User le nouveau nom random qu'on vient de déterminer
                $user->setAvatar($newFilename);
            }
            // sinon ...
            else{
                //...on remplace l'avatar existant par $avatar récupéré précédemment (cf ligne 104 à 110)
                $user->setAvatar($avatar);
            }

            // Comme l'objet existait en BDD, on n'a pas besoin de le persister
            // on appelle l'entity manager qui permet de manipuler les données en BDD et on enregistre (flush)
            $this->getDoctrine()->getManager()->flush();

            // on redirige vers la liste des utilisateurs
            return $this->redirectToRoute('admin_user_browse');
        }
         // si les données du formulaire sont invalides et/ou non soumises, alors on affiche le formulaire
        return $this->render('admin/user/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Méthode permettant de changer le status de l'utilisateur et non pas de supprimer l'utilisateur
     * @Route("/delete/{id}", name="delete", requirements={"id"="\d+"})
     */
    public function delete(User $user, Request $request)
    {
        // on vérifie l'existence d'un token
        $token = $request->request->get('_token');

        // si le token est valide: comparaison entre le token généré et le token reçu de la requete
        if ($this->isCsrfTokenValid('deleteUser', $token)) {
            // ... alors on change le status de l'objet User
            $user->setStatus('2');
            // on appelle l'entity manager qui permet de manipuler les données en BDD
            $em = $this->getDoctrine()->getManager();
            // on enregistre en BDD
            $em->flush();
            // on redirige sur la liste des utilisateurs
            return $this->redirectToRoute('admin_user_browse');
        }

        // Si le token n'est pas valide, on lance une exception Access Denied
        throw $this->createAccessDeniedException('Le token n\'est pas valide.');
    }
}