<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Form\UtilisateurType;
use App\Service\FlashMessageHelperInterface;
use Monolog\Handler\Curl\Util;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use App\Service\UtilisateurManagerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UtilisateurController extends AbstractController
{
    #[Route('/inscription', name: 'inscription', methods: ["GET", "POST"])]
    public function inscription(UtilisateurManagerInterface $utilisateurManager, FlashMessageHelperInterface $flashHelper, Request $request, EntityManagerInterface $entityManager): Response
    {
        if($this->isGranted('ROLE_USER')) {
            return $this->redirectToRoute('feed');
        }

        $utilisateur = new Utilisateur();

        $form = $this->createForm(UtilisateurType::class, $utilisateur, [
            'method' => 'POST',
            'action' => $this->generateURL('inscription')
        ]);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {

            $utilisateurManager->proccessNewUtilisateur($utilisateur, $form["plainPassword"]->getData(), $form["fichierPhotoProfil"]->getData());
            // À ce stade, le formulaire et ses données sont valides
            // L'objet "Exemple" a été mis à jour avec les données, il ne reste plus qu'à le sauvegarder
            $entityManager->persist($utilisateur);
            $entityManager->flush();

            //On redirige vers la page suivante
            return $this->redirectToRoute('feed');
        }

        $flashHelper->addFormErrorsAsFlash($form);

        return $this->render('utilisateur/inscription.html.twig', [
            'form' => $form
        ]);
    }
    #[Route('/connexion', name: 'connexion', methods: ["GET", "POST"])]
    public function connexion(AuthenticationUtils $authenticationUtils): Response
    {
        if($this->isGranted('ROLE_USER')) {
            return $this->redirectToRoute('feed');
        }

        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('utilisateur/connexion.html.twig', [
            'last_username' => $lastUsername
        ]);
    }
    #[IsGranted('ROLE_USER')]
    #[Route('/deconnexion', name: 'deconnexion', methods: ['POST'])]
    public function deconnexion(): never
    {
        //Ne sera jamais appelée
        throw new \Exception("Cette route n'est pas censée être appelée. Vérifiez security.yaml");
    }

    #[Route('/utilisateurs/{login}/feed', name: 'pagePerso', methods: ["GET"])]
    public function pagePerso(#[MapEntity] ?Utilisateur $user, FlashMessageHelperInterface $flashHelper, RequestStack $requestStack): Response
    {
        if($user !== $this->getUser()) {
            $flashBag = $requestStack->getSession()->getFlashBag();
            $flashBag->add("error", "L'utilisateur n'existe pas");
            return $this->redirectToRoute('feed');
        }

        return $this->render('utilisateur/page_perso.html.twig', [
            'user' => $user
        ]);
    }
}
