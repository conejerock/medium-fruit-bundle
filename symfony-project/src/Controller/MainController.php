<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    #[Route('/', name: 'app_main')]
    public function index(): JsonResponse
    {
        return $this->json([
            'title' => 'This is my main controller',
            'message' => 'We\'re trying to create a fruit bundle',
        ]);
    }
}
