<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

class ImageUploader
{
    /**
     * Déplace un fichier reçu dans une requête
     *
     * @param Request $request La requête de laquelle extraire l'image
     * @param string $fieldName Le nom du champs contenant le fichier image
     * @return string Le nouveau nom du fichier
     */
    public function upload(Request $request, string $fieldName, string $fileName = null)
    {
        // On récupère l'objet UploadedFile dans la requête
        $imageFile = $request->files->get($fieldName);
        //si $imageFile existe
        if ($imageFile !== null) {
            // On détermine le nom du fichier à placer dans le dossier public
            // L'opérateur null coalescent «??» va tester la valeur à sa gauche, si elle est différente de null,
            // c'est cette valeur qu'on affecte à $newFileName. Si elle est null, on affectera ce qui se trouve
            // à droite de l'opérateur
            $newFileName = $fileName ?? $this->createFileName($imageFile);
            // On demande à placer notre fichier uploadé dans le dossier public/images
            // en précisant le nouveau nom du fichier
            // On utilise la variable d'environnement ADS_IMAGES_DIRECTORY dans nos fichiers .env(.local)
            $imageFile->move($_ENV['ADS_IMAGES_DIRECTORY'], $newFileName);
            // On retourne le nouveau nom du fichier pour qu'il soit utilisé dans le controleur
            return $newFileName;
        }
        // Si aucun fichier n'a été transmis, on retourne $fileName
        // Si $fileName vaut null, alors on retourne null
        return $fileName;
    }

    public function createFileName(UploadedFile $file)
    {
        return uniqid() . '.' .$file->guessClientExtension();
    }
}
    
