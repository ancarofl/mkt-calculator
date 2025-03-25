<?php

namespace App\Controller;


use App\Repository\TemperatureDatasetRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Form\TemperatureDatasetUploadType;

#[Route('/datasets/temperature', name: 'datasets_temperature_')]
class TemperatureDatasetController extends AbstractController
{
	#[Route('/', name: 'index', methods: ['GET'])]
	public function index(TemperatureDatasetRepository $datasetRepo): Response
	{
		$datasets = $datasetRepo->findAll();

		return $this->render('datasets/index.html.twig', [
			'datasets' => $datasets
		]);
	}

	#[Route('/new', name: 'new', methods: ['GET'])]
	public function new(): Response
	{
		$form = $this->createForm(TemperatureDatasetUploadType::class);

		return $this->render('datasets/new.html.twig', [
			'form' => $form->createView(),
		]);
	}

	#[Route('/', name: 'create', methods: ['POST'])]
	public function create(
		Request $request,
	): JsonResponse {
		return new JsonResponse(
			[
				'message' => 'TODO: IMPLEMENT THIS!',
			],
			Response::HTTP_INTERNAL_SERVER_ERROR
		);
	}
}
