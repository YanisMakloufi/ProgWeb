<?php
namespace App\Service;

use App\Entity\Utilisateur;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Stripe\StripeClient;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PaymentHandler implements PaymentHandlerInterface
{

    public function __construct(private RouterInterface $router,
                                #[Autowire('%premium_price%')] private int $premium_price,
                                #[Autowire('%cle_stripe%')] private string $cle_stripe,
                                private EntityManagerInterface $entityManager,
                                private UtilisateurRepository $utilisateurRepository)
    {
    }

    public function getPremiumCheckoutUrlFor(Utilisateur $utilisateur)  : string {
        $paymentData = [
            'mode' => 'payment',
            'payment_intent_data' => ['capture_method' => 'manual', 'receipt_email' => $utilisateur->getAdresseEmail()],
            'customer_email' => $utilisateur->getAdresseEmail(),
            'success_url' => $this->router->generate('premiumCheckoutConfirm', [], UrlGeneratorInterface::ABSOLUTE_URL).'?idStripe={CHECKOUT_SESSION_ID}',
            'cancel_url' => $this->router->generate("premiumInfos", [], UrlGeneratorInterface::ABSOLUTE_URL),
            "metadata" => ["utilisateurId" => $utilisateur->getId(),
                            "student_token" => "YanisM"],
            "line_items" => [
                [
                    "price_data" => [
                        "currency" => "eur",
                        "product_data" => ["name" => 'The Feed Premium'],
                        "unit_amount" => $this->premium_price*100
                    ],
                    "quantity" => '1'
                ],
            ]
        ];
        Stripe::setApiKey($this->cle_stripe);
        $stripeSession = Session::create($paymentData);
        $url = $stripeSession->url;
        return $url;
    }

    public function handlePaymentPremium(Session $session) : void {
        $metadata = $session["metadata"];

        //Avant d'extraire une donnée, on peut bien sûr vérifier sa présence...
        if(!isset($metadata["utilisateurId"])) {
            throw new \Exception("utilisateurId manquant...");
        }
        if(!isset($metadata["student_token"]) || $metadata["student_token"] !== "YanisM") {
            throw new \Exception("Requête d'un autre étudiant...");
        }

        //L'objet "paymentIntent" permet de capturer (confirmer) ou d'annuler le paiement.
        $paymentIntent = $session["payment_intent"];
        //Pour réaliser ces opérations, on a besoin d'un objet StripeClient initialisé avec notre clé secrète d'API.
        $stripe = new StripeClient($this->cle_stripe);

        //Pour annuler le paiement
        //$stripe->paymentIntents->cancel($paymentIntent);

        //Pour "capturer" et valider le paiement
        $paymentCapture = $stripe->paymentIntents->capture($paymentIntent, []);

        //Après avoir fait diverses vérifications et avoir capturé le paiement avec succès, on peut réaliser nos actions complémentaires (dans notre cas, mettre l'attribut "premium" de l'utilisateur cible à true, puis sauvegarder).
        $userId = $metadata["utilisateurId"];
        $user = $this->utilisateurRepository->find($userId);
        if(is_null($user)){
            $stripe->paymentIntents->cancel($paymentIntent);
            throw new \Exception("L'utilisateur n'existe pas");
        }

        if($user->isPremium()){
            $stripe->paymentIntents->cancel($paymentIntent);
            throw new \Exception("L'utilisateur est déjà premium");
        }

        //On peut ensuite vérifier si le paiement a bien été capturé (si oui, on dispose de l'argent sur le compte Stripe, à ce stade).
        if($paymentCapture == null || $paymentCapture["status"] != "succeeded") {
            $stripe->paymentIntents->cancel($paymentIntent);
            throw new \Exception("Le paiement n'a pas pu être complété...");
        }

        $user->setPremium(true);
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    public function checkPaymentStatus($sessionId) : bool {
        //On initialise le client Stripe avec notre clé secrète
        $stripe = new StripeClient($this->cle_stripe);

        //On récupère les données de la session à partir de l'identifiant de la session
        $session = $stripe->checkout->sessions->retrieve($sessionId);

        //On extraie l'identifiant du paiement depuis la session
        $paymentIntentId = $session->payment_intent;

        //On récupère les données du paiement
        $paymentIntent = $stripe->paymentIntents->retrieve($paymentIntentId);

        //L'état "succeeded" signifie que le paiement a bien été capturé (le client a été débité)
        return $paymentIntent->status;
    }
}
