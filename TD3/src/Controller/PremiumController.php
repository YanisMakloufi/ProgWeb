<?php

namespace App\Controller;

use App\Service\PaymentHandlerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class PremiumController extends AbstractController
{
    #[IsGranted(new Expression("is_granted('ROLE_USER') and not user.isPremium()"))]
    #[Route('/premium', name: 'premiumInfos', methods: ['GET'])]
    public function premiumInfos(): Response
    {
        return $this->render('premium/premium-infos.html.twig', []);
    }

    #[Route('/premium/checkout', name: 'premiumCheckout', methods: ['GET'])]
    public function premiumCheckout(PaymentHandlerInterface $paymentHandler): Response
    {
        return $this->redirect($paymentHandler->getPremiumCheckoutUrlFor($this->getUser()));
    }
}
