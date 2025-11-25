<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\StatisticsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomepageController extends AbstractController
{
    public function __construct(
        private readonly StatisticsService $statisticsService,
    ) {
    }

    #[Route('/', name: 'app_homepage')]
    public function index(): Response
    {
        $statistics = $this->statisticsService->getHomepageStatistics();

        return $this->render('homepage/index.html.twig', [
            'stats' => $statistics,
            'service' => $this->statisticsService,
        ]);
    }
}
