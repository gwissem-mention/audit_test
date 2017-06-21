<?php

namespace Search\Controller;

use Search\Service\RequestToQueryTransformer;
use Search\Service\SearchRepository;
use Search\Service\UserRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

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
     * SearchController constructor.
     * @param SearchRepository $repository
     * @param UserRepository $userRepository
     * @param RequestToQueryTransformer $transformer
     */
    public function __construct(SearchRepository $repository, UserRepository $userRepository, RequestToQueryTransformer $transformer)
    {
        $this->searchRepository = $repository;
        $this->userRepository = $userRepository;
        $this->transformer = $transformer;
    }

    /**
     * Common search action
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function indexAction(Request $request)
    {
        $data = $this->searchRepository->search(
            $this->transformer->transform($request),
            $this->userRepository->getUserByToken($request->query->get('token'))
        );

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
        $data = $this->searchRepository->searchHot(
            $this->transformer->transform($request),
            $this->userRepository->getUserByToken($request->query->get('token'))
        );

        return new JsonResponse($data);
    }
}
