<?php

namespace App\Form;

use App\Entity\Utilisateur;
use MongoDB\BSON\Regex;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

class UtilisateurType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('login', TextType::class)
            ->add('adresseEmail', EmailType::class)
            ->add('plainPassword', PasswordType::class, ["mapped" => false, "constraints" => [new NotBlank(), new NotNull()]])
            ->add('fichierPhotoProfil', FileType::class, ["mapped" => false, "constraints" => [new File(maxSize: '10M', maxSizeMessage: "L'image envoyé dépasse les 10Mo", extensions: ['jpg', 'png'], extensionsMessage: "Le fichier n'est pas un .png ni un .jpg")]])
            ->add('inscription', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Utilisateur::class,
        ]);
    }
}
