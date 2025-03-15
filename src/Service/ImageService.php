<?php

namespace App\Service;

use App\Medoo\Repository\CreatedImagesRepository;
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

    public function  __construct(
        LoggerInterface         $appLogger,
        CreatedImagesRepository $createdImagesRepository,
        UploadImagesRepository  $uploadImagesRepository,
        StorageImagesRepository $storageImagesRepository,
    )
    {
        $this->appLogger = $appLogger;
        $this->createdImagesRepository = $createdImagesRepository;
        $this->uploadImagesRepository = $uploadImagesRepository;
        $this->storageImagesRepository = $storageImagesRepository;
    }

    public function getImageList(): array
    {
        // 해당몰에 관련된 모든 이미지 조회해온다
        $mallId = Context::getMallId();
        $where = [
            'mall_id' => $mallId,
        ];
        // 아이디 역순으로 노출
        $where['ORDER'] = ['id' => 'DESC'];

        // 3분류 이미지 조회해온 후 처리진행
        $storageList = $this->storageImagesRepository->select($where, ['id', 'url', 'origin_type', 'origin_id']);

        $where['is_deleted'] = 'F';
        $createdList = $this->createdImagesRepository->select($where, ['id', 'url', 'create_type']);
        $uploadList = $this->uploadImagesRepository->select($where, ['id', 'url']);

        return ['created' => $createdList, 'upload' => $uploadList, 'storage' => $storageList];
    }

    /**
     * 보관함 이미지 데이터를 처리
     * @param $storageList
     * @return array
     */
    private function getStorageOriginIds($storageList): array
    {
        if (ArrayUtil::isValidArray($storageList) === false) return ['create' => [], 'upload' => []];

        // 배열에 내용이 있을시만 처리한다
        $fromCreated = $fromUpload = [];
        foreach ($storageList as $storageInfo) {
            $originType = ArrayUtil::getVal('origin_type',$storageInfo);
            if ($originType === 'created') {
                $fromCreated[] = ArrayUtil::getVal('origin_id',$storageInfo);
            } elseif ($originType === 'upload') {
                $fromUpload[] = ArrayUtil::getVal('origin_id',$storageInfo);
            }
        }
        return ['create' => $fromCreated, 'upload' => $fromUpload];
    }

    /**
     * 이미지 리스트에 보관함에 여부 값을 넣어준다
     * @param $imageList
     * @param $originIds
     * @return array
     */
    private function getImageResult($imageList, $originIds): array
    {
        $result = [];
        // 배열에 내용이 있을시만 처리한다
        foreach ($imageList as $imageInfo) {
            $tmp = $imageInfo;
            $id = ArrayUtil::getVal('id', $imageInfo);
            // 보관함에 관련된 이미지가 있으면 is_storage = T
            if (ArrayUtil::isValidArray($originIds) && in_array($id, $originIds)) {
                $tmp['is_storage'] = 'T';
            } else {
                $tmp['is_storage'] = 'F';
            }
            $result[] = $tmp;
        }
        return $result;
    }

    public function deleteCreateImages($param): void
    {
        $ids = ArrayUtil::getVal('id', $param);
        if (ArrayUtil::isValidArray($ids)) {
            // 생성한 이미지를 삭제한다
            $this->createdImagesRepository->update(['is_deleted' => 'T', 'deleted_date' => date('Y-m-d H:i:s')],['mall_id' => Context::getMallId(), 'id' => $ids]);
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

    public function deleteStorageImages($param): void
    {
        // 보관함 이미지를 삭제한다
        $ids = ArrayUtil::getVal('id', $param);
        if (ArrayUtil::isValidArray($ids)) {
            // 보관함 이미지를 삭제한다
            $this->storageImagesRepository->delete(['mall_id' => Context::getMallId(), 'id' => $ids]);
        }
    }

    public function createMoveToStorage($param): void
    {
        // 생성한 이미지를 보관함으로 이동한다
        $imageList = ArrayUtil::getVal('image_list', $param);
        $this->getImgInsertData($imageList, 'created', Context::getMallId());
    }

    public function uploadMoveToStorage($param): void
    {
        // 업로드 이미지를 보관함으로 이동한다
        $imageList = ArrayUtil::getVal('image_list', $param);
        $this->getImgInsertData($imageList, 'upload', Context::getMallId());
    }

    private function getImgInsertData($imageList, $originType, $mallId): array
    {
        // 처리할 이미지가 없으면 종료
        if (ArrayUtil::isValidArray($imageList) === false) return [];

        $insertData = $ids = [];
        foreach ($imageList as $imageInfo) {
            $url = ArrayUtil::getVal('url', $imageInfo);
            if (!$url) continue;
            $originId = ArrayUtil::getVal('id', $imageInfo);
            $insertData[] = [
                'mall_id' => $mallId,
                'url' => $url,
                'add_date' => date('Y-m-d H:i:s'),
                'origin_type' => $originType,
                'origin_id' => $originId,
            ];
            // id를 기록함
            $ids[] = $originId;
        }
        // 추가할 데이터가 있으면 추가
        if (ArrayUtil::isValidArray($insertData)) {
            $this->storageImagesRepository->insert($insertData);
        }

        return $ids;
    }
}