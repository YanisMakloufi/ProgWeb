<?php
namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Http\Event\LoginFailureEvent;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;
use Symfony\Component\Security\Http\Event\LogoutEvent;

class AuthenticationSubscriber {

    public function __construct(private RequestStack $requestStack){}

    #[AsEventListener]
    public function LoginSuccessEvent (LoginSuccessEvent $event) {
        $flashBag = $this->requestStack->getSession()->getFlashBag();
        $flashBag->add("success", "Vous etes désormais connecté");    }

    #[AsEventListener]
    public function LoginFailureEvent (LoginFailureEvent $event) {
        $flashBag = $this->requestStack->getSession()->getFlashBag();
        $flashBag->add("error", "Votre connexion a écouhé");    }

    #[AsEventListener]
    public function LogoutEvent (LogoutEvent $event) {
        $flashBag = $this->requestStack->getSession()->getFlashBag();
        $flashBag->add("success", "Vous etes déconnecté avec succès");    }

}