<?php

namespace App\Controller;

use App\Entity\TemperatureDataset;
use App\Repository\TemperatureDatasetRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Routing\Annotation\Route;

#[Route('/debug', name: 'debug_')]
class DebugController extends AbstractController
{
	#[Route('/datasets', name: 'datasets_create', methods: ['POST'])]
	public function createDataset(TemperatureDatasetRepository $repository): JsonResponse
	{
		$dataset = new TemperatureDataset('Test set ' . rand(100, 999));

		$repository->save($dataset, true);

		return new JsonResponse([
			'message' => 'Data set created successfully',
			'data' => [
				'id' => $dataset->getId(),
			]
		], Response::HTTP_CREATED);
	}
}
