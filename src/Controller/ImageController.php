<?php

namespace App\Controller;

use App\Service\ImageService;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ImageController extends BaseController
{
    private ImageService $imageService;

    public function __construct(
        RequestStack    $request,
        LoggerInterface $appLogger,
        ImageService    $imageService,
    ) {
        $this->imageService = $imageService;
        parent::__construct($request, $appLogger);
    }

    #[Route('/api/image/list', methods: 'GET')]
    public function getImageList(): Response
    {
        $result = $this->imageService->getImageList();
        return $this->response('success', $result);
    }

    #[Route('/api/insert/created', methods: 'POST')]
    public function insertCreateImages(): Response
    {
        $param = $this->getContentParams();
        $this->imageService->insertCreateImages($param);
        return $this->response('success', []);
    }

    #[Route('/api/delete/created', methods: 'PUT')]
    public function deleteCreateImages(): Response
    {
        $param = $this->getContentParams();
        $this->imageService->deleteCreateImages($param);
        return $this->response('success', []);
    }

    #[Route('/api/insert/upload', methods: 'POST')]
    public function insertUploadImages(): Response
    {
        $param = $this->getContentParams();
        $this->imageService->insertUploadImages($param);
        return $this->response('success', []);
    }

    #[Route('/api/delete/upload', methods: 'PUT')]
    public function deleteUploadImages(): Response
    {
        $param = $this->getContentParams();
        $this->imageService->deleteUploadImages($param);
        return $this->response('success', []);
    }

    #[Route('/api/insert/storage', methods: 'POST')]
    public function insertStorageImg(): Response
    {
        $param = $this->getContentParams();
        $this->imageService->insertStorageImg($param);
        return $this->response('success', []);
    }

    #[Route('/api/delete/storage', methods: 'PUT')]
    public function deleteStorageImages(): Response
    {
        $param = $this->getContentParams();
        $this->imageService->deleteStorageImages($param);
        return $this->response('success', []);
    }

    #[Route('/api/reference/images', methods: 'GET')]
    public function getReferenceImages(): Response
    {
        $param = $this->getQueryParams();
        $result = $this->imageService->getReferenceImages($param);
        return $this->response('success', $result);
    }
}