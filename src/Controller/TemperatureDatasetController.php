<?php

namespace App\Controller;

use App\Repository\TemperatureDatasetRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\TemperatureDatasetUploadType;
use App\Entity\TemperatureDataset;
use App\Service\FileParser\FileParserInterface;
use App\Service\TemperatureDatasetProcessor;
use Exception;
use Doctrine\DBAL\Exception as DBALException;

#[Route('/datasets/temperature', name: 'datasets_temperature_')]
class TemperatureDatasetController extends AbstractController
{
	#[Route('/', name: 'index', methods: ['GET'])]
	public function index(TemperatureDatasetRepository $datasetRepo, Request $request): Response
	{
		$page = $request->query->getInt('page', 1);
		$limit = 30;
		$offset = ($page - 1) * $limit;

		$datasets = $datasetRepo->findBy([], ['id' => 'ASC'], $limit, $offset);

		$totalItems = $datasetRepo->count([]);
		$totalPages = ceil($totalItems / $limit);

		return $this->render('datasets/index.html.twig', [
			'datasets' => $datasets,
			'currentPage' => $page,
			'totalPages' => $totalPages,
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
		FileParserInterface $fileParser,
		TemperatureDatasetRepository $datasetRepository,
		TemperatureDatasetProcessor $datasetProcessor
	): Response {
		$form = $this->createForm(TemperatureDatasetUploadType::class);
		$form->handleRequest($request);

		// TODO: DISPLAY ERRORS
		if (!$form->isSubmitted() || !$form->isValid()) {
			$errors = [];
			foreach ($form->getErrors(true) as $error) {
				$errors[] = $error->getMessage();
			}
			return $this->json([
				'message' => 'Invalid form submission',
				'errors' => $errors,
			], Response::HTTP_BAD_REQUEST);
		}

		$data = $form->getData();

		$dataset = new TemperatureDataset($data['name']);
		$datasetRepository->save($dataset, true);

		try {
			$records = $fileParser->parse($data['file']);
			$result = $datasetProcessor->processDataset($dataset->getId(), $records);

			$dataset->setCalculatedMkt($result['mkt']);
			$datasetRepository->save($dataset, true);

			// TODO: Highlight just added dataset? Add a loading bar while processing?
			return $this->redirectToRoute('datasets_temperature_index');
		} catch (DBALException $e) {
			return $this->json([
				'error' => 'Database error: ' . $e->getMessage(),
			], Response::HTTP_INTERNAL_SERVER_ERROR);
		} catch (Exception $e) {
			return $this->json([
				'error' => 'An unexpected error occurred: ' . $e->getMessage(),
			], Response::HTTP_INTERNAL_SERVER_ERROR);
		}
	}
}
