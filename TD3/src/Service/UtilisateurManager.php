<?php
namespace App\Service;

use App\Entity\Utilisateur;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UtilisateurManager implements UtilisateurManagerInterface {

    public function __construct(
        #[Autowire('%dossier_photo_profil%')] private string $dossier_photo_profil,
        private UserPasswordHasherInterface $passwordHasher
    ){}

    /**
     * Chiffre le mot de passe puis l'affecte au champ correspondant dans la classe de l'utilisateur
     */
    private function chiffrerMotDePasse(Utilisateur $utilisateur, ?string $plainPassword) : void {
        $hashed = $this->passwordHasher->hashPassword($utilisateur, $plainPassword);
        $utilisateur->setPassword($hashed);
    }

    /**
     * Sauvegarde l'image de profil dans le dossier de destination puis affecte son nom au champ correspondant dans la classe de l'utilisateur
     */
    private function sauvegarderPhotoProfil(Utilisateur $utilisateur, ?UploadedFile $fichierPhotoProfil) : void {
        if($fichierPhotoProfil != null) {
            $fileName = uniqid() . '.' . $fichierPhotoProfil->guessExtension();
            $fichierPhotoProfil->move($this->dossier_photo_profil, $fileName);
            $utilisateur->setNomPhotoProfil($fileName);
        }
    }

    /**
     * Réalise toutes les opérations nécessaires avant l'enregistrement en base d'un nouvel utilisateur, après soumissions du formulaire (hachage du mot de passe, sauvegarde de la photo de profil...)
     */
    public function proccessNewUtilisateur(Utilisateur $utilisateur, ?string $plainPassword, ?UploadedFile $fichierPhotoProfil) : void {
        $this->chiffrerMotDePasse($utilisateur, $plainPassword);
        $this->sauvegarderPhotoProfil($utilisateur, $fichierPhotoProfil);
        //On sauvegarde (et on déplace) l'image de profil
    }

}