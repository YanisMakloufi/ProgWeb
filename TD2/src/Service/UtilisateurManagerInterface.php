<?php

namespace App\Service;

use App\Entity\Utilisateur;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RequestStack;

interface UtilisateurManagerInterface
{
    public function proccessNewUtilisateur(Utilisateur $utilisateur, ?string $plainPassword, ?UploadedFile $fichierPhotoProfil) : void;
}