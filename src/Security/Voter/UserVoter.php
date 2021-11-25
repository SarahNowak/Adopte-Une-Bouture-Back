<?php

namespace App\Security\Voter;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class UserVoter extends Voter
{
    /**
     * Retourne un booléen qui indique si notre Voter s'oocupe du droit demandé sur le type d'objet demandé
     *
     * @param string $attribute Le type d'action demandé sur l'objet
     * @param $subject L'objet pour lequel on demande si l'action est autorisé
     * @return boolean
     */
    protected function supports(string $attribute, $subject): bool
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, ['USER_EDIT', 'USER_READ'])
            && $subject instanceof User;
    }

    /**
     * Si supports() a répondu TRUE, Symfony exécute voteOnAttribute()
     * Cette fonction répondu true pour autoriser le droit demandé, false sinon
     */
    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }
        
        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {
            case 'USER_READ':
                // logic to determine if the user can READ
                // si l'utilisateur est l'utilisateur connecté il peut lire ses infos
                if ($subject->getUserIdentifier() === $token->getUserIdentifier()){
                    return true;
                };      
                // return true or false
                break;
            case 'USER_EDIT':
                // logic to determine if the user can EDIT
                 // si l'utilisateur est l'utilisateur connecté il peut modifier ses infos
                if ($subject->getUserIdentifier() === $token->getUserIdentifier()){
                    return true;
                };
                // return true or false
                break;
            case 'USER_DELETE':
                // logic to determine if the user can DELETE
                 // si l'utilisateur est l'utilisateur connecté ou l'utilisateur à un rôle ADMIN, il peut supprimer ses infos
                if ($subject->getUserIdentifier() === $token->getUserIdentifier() || $user->getRoles() === ['ROLE_ADMIN']){
                    return true;
                };
                // return true or false
                break;
        }

        return false;
    }
}
