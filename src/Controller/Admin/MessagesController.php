<?php

namespace App\Controller\Admin;

use App\Entity\Messages;
use App\Form\Admin\MessagesAdminType;
use App\Repository\MessagesRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/messages", name="admin_messages_")
 */
class MessagesController extends AbstractController
{
    /**
     * Méthode listant tous les messages
     * @Route("", name="browse")
     */
    public function browse(MessagesRepository $messagesRepository): Response // on 'typehint' MessageRepository afin d'acceder aux méthodes de recherche comme findAll
    {
        // on retourne les objets dans notre vue TWIG via le render, en parametre 1 : le chemin dans le dossier template, parametre 2 : un tableau de données avec tous nos objets messages
        return $this->render('admin/messages/browse.html.twig', [
            'messages' => $messagesRepository->findAll(),
        ]);
    }

    /**
     * Méthode listant un message
     * On met en paramètre $message qu'on typehint comme étant un objet de la classe Message
     * Ça active le ParamConverter de Symfony qui va récupérer tout seul l'objet Message de la BDD
     * à partir de l'id fourni en URL
     * @Route("/{id}", name="read", requirements={"id"="\d+"})
     */
    public function read(Messages $messages): Response // on donne au param converter (entre parenthese), l'objet concerné, ici $messages. l'ID est récupéré directement dans le nom de la route
    {
        // on retourne à notre vue les informations de cette objet (1: chemin , 2: tableau des données de l'objet)
        return $this->render('admin/messages/read.html.twig', [
            'messages' => $messages,
        ]);
    }

    /**
     * Méthode permettant d'ajouter un message
     * @Route("/add", name="add")
     */
    public function add(Request $request): Response
    {
        // on crée notre nouvel objet message
        $messages = new Messages();
        // on crée notre formulaire, auquel on associe 1: son type, 2: l'objet concerné
        $form = $this->createForm(MessagesAdminType::class, $messages);
        // on asssocie le formulaire à la requete
        // handlerequest prend les données en Post et les places dans $form puis dans $messages
        $form->handleRequest($request);

        // on vérifie que le formulaire est bien soumis et valide
        // que les contraintes de validation sont respectées et que le token anti-CSRF est bon
        // si ce n'est pas le cas, il ne rentre pas dans les conditions du if
        if($form->isSubmitted() && $form->isValid()) {
            // on appelle l'entity manager qui permet de manipuler les données en BDD
            $em = $this->getDoctrine()->getManager();
            // on persist le nouvel objet message, on le place en file d'attente avant enregistrement
            $em->persist($messages);
            // on prend les objets modifiés (en file d'attente) et on les enregistre dans la BDD
            $em->flush();
            // une fois l'objet enregistré, on redirige ver la page liste des messages
            return $this->redirectToRoute('admin_messages_browse');
        }
        // si les données du formulaire sont invalides et/ou non soumises, alors on affiche le formulaire
        return $this->render('admin/messages/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Méthode permettant la modification d'un message
     * @Route("/edit/{id}", name="edit", requirements={"id"="\d+"})
     */
    public function edit(Messages $messages, Request $request): Response
    {
        // on crée notre formulaire, auquel on associe 1: son type, 2: l'objet concerné 
        $form = $this->createForm(MessagesAdminType::class, $messages);
        // on asssocie le formulaire à la requete
        // handlerequest prend les données en Post et les places dans $form puis dans $messages
        $form->handleRequest($request);

        // on vérifie que le formulaire est bien soumis et valide
        // que les contraintes de validation sont respectées et que le token anti-CSRF est bon
        // si ce n'est pas le cas, il ne rentre pas dans les conditions du if
        if($form->isSubmitted() && $form->isValid()) {
            // Comme l'objet existait en BDD, on n'a pas besoin de le persister
            // on appelle l'entity manager qui permet de manipuler les données en BDD et on enregistre (flush)
            $this->getDoctrine()->getManager()->flush();
            // on redirige vers la liste des messages
            return $this->redirectToRoute('admin_messages_browse');
        }
        // si les données du formulaire sont invalides et/ou non soumises, alors on affiche le formulaire
        return $this->render('admin/messages/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Méthode permettant de changer le status du message et non pas de supprimer le message
     * @Route("/delete/{id}", name="delete", requirements={"id"="\d+"})
     */
    public function delete(Messages $messages, Request $request)
    {
        // on vérifie l'existence d'un token
        $token = $request->request->get('_token');
        
        // si le token est valide: comparaison entre le token généré et le token reçu de la requete
        if ($this->isCsrfTokenValid('deleteMessage', $token)) {
            // ... alors on change le status de l'objet Message
            $messages->setStatus('2');
            // on appelle l'entity manager qui permet de manipuler les données en BDD
            $em = $this->getDoctrine()->getManager();
            // on enregistre en BDD
            $em->flush();
            // on redirige sur la liste des messages
            return $this->redirectToRoute('admin_messages_browse');
        }

        // Si le token n'est pas valide, on lance une exception Access Denied
        throw $this->createAccessDeniedException('Le token n\'est pas valide.');
    }
}
