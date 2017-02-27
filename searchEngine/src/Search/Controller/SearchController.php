<?php

namespace Search\Controller;

use Search\Service\RequestToQueryTransformer;
use Search\Service\SearchRepository;
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
    protected $repository;

    /**
     * @var RequestToQueryTransformer
     */
    protected $transformer;

    /**
     * SearchController constructor.
     * @param SearchRepository $repository
     * @param RequestToQueryTransformer $transformer
     */
    public function __construct(SearchRepository $repository, RequestToQueryTransformer $transformer)
    {
        $this->repository = $repository;
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
        $data = $this->repository->search(
            $this->transformer->transform($request)
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
        $data = $this->repository->searchHot(
            $this->transformer->transform($request)
        );

        return new JsonResponse($data);
    }
}
