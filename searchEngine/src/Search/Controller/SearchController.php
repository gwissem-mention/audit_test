<?php

namespace Search\Controller;

use Search\Service\RequestToQueryTransformer;
use Search\Service\SearchRepository;
use Search\Service\SearchStatsRepository;
use Search\Service\UserRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\DateTime;

/**
 * Search controller
 */
class SearchController
{
    /**
     * @var SearchRepository
     */
    protected $searchRepository;
    /**
     * @var UserRepository
     */
    protected $userRepository;

    /**
     * @var RequestToQueryTransformer
     */
    protected $transformer;

    /**
     * @var SearchStatsRepository
     */
    protected $statsRepository;

    /**
     * SearchController constructor.
     * @param SearchRepository $repository
     * @param UserRepository $userRepository
     * @param RequestToQueryTransformer $transformer
     * @param SearchStatsRepository $statsRepository
     */
    public function __construct(SearchRepository $repository, UserRepository $userRepository, RequestToQueryTransformer $transformer, SearchStatsRepository $statsRepository)
    {
        $this->searchRepository = $repository;
        $this->userRepository = $userRepository;
        $this->transformer = $transformer;
        $this->statsRepository = $statsRepository;
    }

    /**
     * Common search action
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function indexAction(Request $request)
    {
        $query = $this->transformer->transform($request);
        $token = $request->query->get('token');

        $data = $this->searchRepository->search(
            $query,
            $this->userRepository->getUserByToken($token)
        );

        $this->statsRepository->insertSearch($query, $token, $data['hits']['total'], true);

        return new JsonResponse($data);
    }

    /**
     * Hot search action
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function hotAction(Request $request)
    {
        $query = $this->transformer->transform($request);
        $token = $request->query->get('token');

        $data = $this->searchRepository->searchHot(
            $query,
            $this->userRepository->getUserByToken($token)
        );

        $this->statsRepository->insertSearch($query, $token, $data['hits']['total'], false);

        return new JsonResponse($data);
    }
}
