<?php
namespace App\EventListener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Symfony\Component\HttpFoundation\RequestStack;

class JWTCreatedListener
{
    /**
     * @param JWTCreatedEvent $event
     * @return void
     */
    public function onJWTCreated(JWTCreatedEvent $event)
    {
        $payload = $event->getData();
        //Insertion de données ICI - A compléter

        $payload["id"] = $event->getUser()->getId();
        $payload["adresseEmail"] = $event->getUser()->getAdresseEmail();
        $payload["premium"] = $event->getUser()->isPremium();
        $event->setData($payload);
    }
}