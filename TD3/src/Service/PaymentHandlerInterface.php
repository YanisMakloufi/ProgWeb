<?php

namespace App\Service;

use App\Entity\Utilisateur;
use Stripe\Checkout\Session;

interface PaymentHandlerInterface
{
    public function getPremiumCheckoutUrlFor(Utilisateur $utilisateur): string;

    public function handlePaymentPremium(Session $session) : void;

    public function checkPaymentStatus($sessionId) : bool;

}