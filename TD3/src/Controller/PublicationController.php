<?php

namespace App\Controller;

use App\Form\PublicationType;
use App\Repository\PublicationRepository;
use App\Service\FlashMessageHelperInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Publication;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class PublicationController extends AbstractController
{
    #[Route('/', name: 'feed', methods: ["GET", "POST"])]
    public function index(FlashMessageHelperInterface $flashHelper, EntityManagerInterface $entityManager, Request $request, PublicationRepository $publicationRepo): Response
    {
        $publications = $publicationRepo->findAllOrderedByDate();

        $publi = new Publication();

        $form = $this->createForm(PublicationType::class, $publi, [
            'method' => 'POST',
            'action' => $this->generateURL('feed')
        ]);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $publi->setAuteur($this->getUser());
            // À ce stade, le formulaire et ses données sont valides
            // L'objet "Exemple" a été mis à jour avec les données, il ne reste plus qu'à le sauvegarder
            $entityManager->persist($publi);
            $entityManager->flush();

            //On redirige vers la page suivante
            return $this->redirectToRoute('feed');
        }

        $flashHelper->addFormErrorsAsFlash($form);

        return $this->render('publication/feed.html.twig', [
            'publications' => $publications,
            'formulaire' => $form
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/feedy/{id}', name: 'deletePublication ', options: ["expose" => true], methods: ["DELETE"])]
    public function deletePublication(PublicationRepository $publicationRepository, EntityManagerInterface $entityManager, $id): Response
    {
        $publi = $publicationRepository->find($id);

        if(is_null($publi)){
            return new JsonResponse(null, 404);
        }else if($this->getUser() != $publi->getAuteur()){
            return new JsonResponse(null, 403);
        }else {
            $entityManager->remove($publi);
            $entityManager->flush();
            return new JsonResponse(null, 204);
        }
    }
}
