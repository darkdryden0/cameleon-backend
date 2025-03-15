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

    #[Route('/api/delete/created', methods: 'PUT')]
    public function deleteCreateImages(): Response
    {
        $param = $this->getContentParams();
        $this->imageService->deleteCreateImages($param);
        return $this->response('success', []);
    }

    #[Route('/api/delete/upload', methods: 'PUT')]
    public function deleteUploadImages(): Response
    {
        $param = $this->getContentParams();
        $this->imageService->deleteUploadImages($param);
        return $this->response('success', []);
    }

    #[Route('/api/delete/storage', methods: 'PUT')]
    public function deleteStorageImages(): Response
    {
        $param = $this->getContentParams();
        $this->imageService->deleteStorageImages($param);
        return $this->response('success', []);
    }

    #[Route('/api/created/storage', methods: 'POST')]
    public function createMoveToStorage(): Response
    {
        $param = $this->getContentParams();
        $this->imageService->createMoveToStorage($param);
        return $this->response('success', []);
    }

    #[Route('/api/upload/storage', methods: 'POST')]
    public function uploadMoveToStorage(): Response
    {
        $param = $this->getContentParams();
        $this->imageService->uploadMoveToStorage($param);
        return $this->response('success', []);
    }
}