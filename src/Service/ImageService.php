<?php

namespace App\Service;

use App\Medoo\Repository\CreatedImagesRepository;
use App\Medoo\Repository\ReferenceImagesRepository;
use App\Medoo\Repository\StorageImagesRepository;
use App\Medoo\Repository\UploadImagesRepository;
use App\Middleware\Context;
use App\Utils\ArrayUtil;
use Psr\Log\LoggerInterface;

class ImageService
{
    protected LoggerInterface $appLogger;
    private CreatedImagesRepository $createdImagesRepository;
    private UploadImagesRepository $uploadImagesRepository;
    private StorageImagesRepository $storageImagesRepository;
    private ReferenceImagesRepository $referenceImagesRepository;

    public function  __construct(
        LoggerInterface           $appLogger,
        CreatedImagesRepository   $createdImagesRepository,
        UploadImagesRepository    $uploadImagesRepository,
        StorageImagesRepository   $storageImagesRepository,
        ReferenceImagesRepository $referenceImagesRepository,
    )
    {
        $this->appLogger = $appLogger;
        $this->createdImagesRepository = $createdImagesRepository;
        $this->uploadImagesRepository = $uploadImagesRepository;
        $this->storageImagesRepository = $storageImagesRepository;
        $this->referenceImagesRepository = $referenceImagesRepository;
    }

    public function getImageList(): array
    {
        // 해당몰에 관련된 모든 이미지 조회해온다
        $mallId = Context::getMallId();
        $where = [
            'mall_id' => $mallId,
            'is_deleted' => 'F'
        ];
        // 아이디 역순으로 노출
        $where['ORDER'] = ['id' => 'DESC'];

        // 3분류 이미지 조회해온 후 처리진행
        $storageList = $this->storageImagesRepository->select($where, ['id', 'url']);
        $createdList = $this->createdImagesRepository->select($where, ['id', 'url', 'operate_type']);
        $uploadList = $this->uploadImagesRepository->select($where, ['id', 'url']);

        return ['created' => $createdList, 'upload' => $uploadList, 'storage' => $storageList];
    }

    public function insertCreateImages($param): void
    {
        $url = ArrayUtil::getVal('img_url', $param);
        $type = ArrayUtil::getVal('type', $param);
        if ($url && $type) {
            $this->createdImagesRepository->insert([
                'mall_id' => Context::getMallId(),
                'url' => $url,
                'operate_type' => $type,
                'insert_date' => date('Y-m-d H:i:s')
            ]);
        }
    }

    public function deleteCreateImages($param): void
    {
        $ids = ArrayUtil::getVal('id', $param);
        if (ArrayUtil::isValidArray($ids)) {
            // 생성한 이미지를 삭제한다
            $this->createdImagesRepository->update(['is_deleted' => 'T', 'deleted_date' => date('Y-m-d H:i:s')],['mall_id' => Context::getMallId(), 'id' => $ids]);
        }
    }

    public function insertUploadImages($param): void
    {
        $url = ArrayUtil::getVal('img_url', $param);
        if ($url) {
            $this->uploadImagesRepository->insert([
                'mall_id' => Context::getMallId(),
                'url' => $url,
                'insert_date' => date('Y-m-d H:i:s')
            ]);
        }
    }

    public function deleteUploadImages($param): void
    {
        // 업로드 이미지를 삭제한다
        $ids = ArrayUtil::getVal('id', $param);
        if (ArrayUtil::isValidArray($ids)) {
            // 업로드 이미지를 삭제한다
            $this->uploadImagesRepository->update(['is_deleted' => 'T', 'deleted_date' => date('Y-m-d H:i:s')],['mall_id' => Context::getMallId(), 'id' => $ids]);
        }
    }

    public function insertStorageImg($param): void
    {
        // 생성한 이미지를 보관함으로 이동한다
        $imageList = ArrayUtil::getVal('image_list', $param);
        $this->getImgInsertData($imageList, Context::getMallId());
    }

    public function deleteStorageImages($param): void
    {
        // 보관함 이미지를 삭제한다
        $ids = ArrayUtil::getVal('id', $param);
        if (ArrayUtil::isValidArray($ids)) {
            // 보관함 이미지를 삭제한다
            $this->storageImagesRepository->update(['is_deleted' => 'T', 'deleted_date' => date('Y-m-d H:i:s')],['mall_id' => Context::getMallId(), 'id' => $ids]);
        }
    }

    private function getImgInsertData($imageList, $mallId): void
    {
        // 처리할 이미지가 없으면 종료
        if (ArrayUtil::isValidArray($imageList) === false) {
            return;
        }

        $insertData = [];
        foreach ($imageList as $imageInfo) {
            $url = ArrayUtil::getVal('url', $imageInfo);
            if (!$url) continue;
            $insertData[] = [
                'mall_id' => $mallId,
                'url' => $url,
                'insert_date' => date('Y-m-d H:i:s'),
            ];
        }
        // 추가할 데이터가 있으면 추가
        if (ArrayUtil::isValidArray($insertData)) {
            $this->storageImagesRepository->insert($insertData);
        }
    }

    public function getReferenceImages($param): array
    {
        $where = [
            'mall_id' => Context::getMallId(),
        ];
        return $this->referenceImagesRepository->findBy($where);
    }
}