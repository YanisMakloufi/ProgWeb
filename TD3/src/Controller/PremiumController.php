<?php

namespace App\Controller;

use App\Service\PaymentHandlerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\RequestStack;
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

    #[Route('/premium/checkout/confirm', name: 'premiumCheckoutConfirm', methods: ['GET'])]
    public function premiumCheckoutConfirm(Request $request, PaymentHandlerInterface $paymentHandler, RequestStack $requestStack): Response
    {
        $flashBag = $requestStack->getSession()->getFlashBag();

        $idStripe = $request->get('idStripe');
        if($paymentHandler->checkPaymentStatus($idStripe)){
            $flashBag->add("success", "Le paiment a été effectuer");
        }else{
            $flashBag->add("error", "Le paiement n'a pas pu etre effectuer");
        }
        return $this->redirectToRoute('feed');
    }
}
