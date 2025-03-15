<?php

namespace App\Medoo\Repository;

use App\Medoo\MedooConnect;
use App\Utils\ArrayUtil;
use Medoo\Medoo;

abstract class BaseRepository
{
    abstract public function table(): string;

    public function getMedoo($reConnect = false): Medoo
    {
        return MedooConnect::medoo($reConnect);
    }

    public function find(int $id): ?array
    {
        return $this->getMedoo()->get($this->table(), '*', ['id' => $id]);
    }

    public function findOneBy(array $criteria, array $orderBy = null): ?array
    {
        $where = $criteria;
        if ($orderBy !== null) {
            $where['ORDER'] = $orderBy;
        }

        return $this->getMedoo()->get($this->table(), '*', $where);
    }

    public function findBy(array $criteria, array $orderBy = null, array $limit = null): array
    {
        $where = $criteria;
        if ($orderBy !== null) {
            $where['ORDER'] = $orderBy;
        }

        if($limit !== null){
            $where['LIMIT'] = $limit;
        }

        return $this->getMedoo()->select($this->table(), '*', $where);
    }

    public function findAll($orderBy = null): array
    {
        $where = [];
        if ($orderBy !== null) {
            $where['ORDER'] = $orderBy;
        }

        return $this->getMedoo()->select($this->table(), '*', $where);
    }

    public function findValue($where, $column)
    {
        $data = $this->getMedoo()->get($this->table(), [$column], $where);
        return ArrayUtil::getVal($column, $data);
    }

    public function select($where, array|string $columns = '*'): ?array
    {
        return $this->getMedoo()->select($this->table(), $columns, $where);
    }

    public function selectAll(array|string $columns = '*'): ?array
    {
        $where = [];
        return $this->getMedoo()->select($this->table(), $columns, $where);
    }

    public function andWhere(array $where = [], array|string $columns = '*'): ?array
    {
        return $this->getMedoo()->select($this->table(), $columns, ['AND' => $where]);
    }

    public function orWhere(array $where = [], array|string $columns = '*'): ?array
    {
        return $this->getMedoo()->select($this->table(), $columns, ['OR' => $where]);
    }

    public function insert($insertData): ?string
    {
        $this->getMedoo('master')->insert($this->table(), $insertData);
        return $this->getMedoo('master')->id();
    }

    public function update($updateData, array $where = []): int
    {
        $data = $this->getMedoo('master')->update($this->table(), $updateData,$where);
        return $data->rowCount();
    }

    public function delete(array $where = []): int
    {
        $data = $this->getMedoo('master')->delete($this->table(), ["AND" => $where]);
        return $data->rowCount();
    }

    /**
     * @param $where
     * @return int|null
     */
    public function count($where): ?int
    {
        return $this->getMedoo()->count($this->table(), $where);
    }

    /**
     * @return string|null
     */
    public function debug(): ?string
    {
        return $this->getMedoo('master')->last();
    }
}