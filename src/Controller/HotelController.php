<?php

namespace App\Controller;

use App\Repository\HotelRepository;
use App\Repository\ReviewRepository;
use App\Services\BenchmarkService;
use App\Services\OvertimeService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HotelController
{
    private $hotelRepository;

    private $reviewRepository;

    public function __construct(HotelRepository $hotelRepository, ReviewRepository $reviewRepository)
    {
        $this->hotelRepository = $hotelRepository;
        $this->reviewRepository = $reviewRepository;
    }

    /**
     * @Route("/hotel/benchmark", name="Benchmark Endpoint", methods={"GET"})
     */
    public function benchmark(Request $request): JsonResponse
    {
        if (empty($request->query->get('hotel')) || empty($request->query->get('since')) || empty($request->query->get('until'))) {
            return new JsonResponse('', Response::HTTP_BAD_REQUEST);
        }

        if ($this->isValidDate($request->query->get('since')) === false || $this->isValidDate($request->query->get('until')) === false) {
            return new JsonResponse('', Response::HTTP_BAD_REQUEST);
        }

        $since = new \DateTime($request->query->get('since'));
        $until = new \DateTime($request->query->get('until'));

        if ($since > $until) {
            return new JsonResponse('', Response::HTTP_BAD_REQUEST);
        }
        
        $hotelId = $request->query->get('hotel');

        $target = $this->reviewRepository->findByHotelAndDateRange($hotelId, $since, $until);

        $overtime = new OvertimeService();
        $target = $overtime->mapOne($target);
        $target['hotel'] = $hotelId;

        $list = [];
        $hotels = $this->hotelRepository->findAllButOne($hotelId);
        foreach ($hotels as $hotel) {
            $auxiliar = $this->reviewRepository->findByHotelAndDateRange($hotel->getId(), $since, $until);
            $item = $overtime->mapOne($auxiliar);
            $item['hotel'] = $hotel->getId();

            $list[] = $item;
        }

        $benchmark = new BenchmarkService();
        return new JsonResponse($benchmark->map($target, $list), Response::HTTP_ACCEPTED);
    }

    /**
     * @Route("/hotel/overtime", name="Overtime Endpoint", methods={"GET"})
     */
    public function overtime(Request $request): JsonResponse
    {
        if (empty($request->query->get('hotel')) || empty($request->query->get('since')) || empty($request->query->get('until'))) {
            return new JsonResponse('', Response::HTTP_BAD_REQUEST);
        }

        if ($this->isValidDate($request->query->get('since')) === false || $this->isValidDate($request->query->get('until')) === false) {
            return new JsonResponse('', Response::HTTP_BAD_REQUEST);
        }

        $since = new \DateTime($request->query->get('since'));
        $until = new \DateTime($request->query->get('until'));

        if ($since > $until) {
            return new JsonResponse('', Response::HTTP_BAD_REQUEST);
        }

        $list = $this->reviewRepository->findByHotelAndDateRange($request->query->get('hotel'), $since, $until);
        $overtime = new OvertimeService();

        return new JsonResponse($overtime->map($list, $since, $until), Response::HTTP_ACCEPTED);
    }

    /**
     * @param string $date
     * @param string $format
     * @return boolean
     */
    private function isValidDate(string $date, string $format = 'Y-m-d'): bool
    {
        $newDate = \DateTime::createFromFormat($format, $date);
        return $newDate && $newDate->format($format) === $date;
    }
}