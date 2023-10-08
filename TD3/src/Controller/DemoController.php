<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DemoController extends AbstractController
{
    #[Route('/hello', name: 'hello_get', methods:["GET"])]
    public function hello_get(): Response
    {
        return $this->render("demo/demo1.html.twig");
    }

    #[Route('/hello/{nom}', name: 'hello_get2', methods:["GET"])]
    public function hello_get2($nom): Response
    {
        $this->addFlash("success","BRAVO A TOI");
        return $this->render("demo/demo2.html.twig" , ["nom" => $nom]);
    }

    #[Route('/courses', name: 'courses_get', methods:["GET"])]
    public function courses_get(): Response
    {
        return $this->render("demo/demo3.html.twig" , ["listeCourses" => ["lait", "pain", "jus d'orange", "chips", "jambon", "Livre sur Symfony"]]);
    }
}
