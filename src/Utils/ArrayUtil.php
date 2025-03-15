<?php

namespace App\Utils;

class ArrayUtil
{
    public static function isValidArray($array): bool
    {
        return is_array($array) === true && count($array) > 0;
    }

    public static function arrayKeyExists($key, $array): bool
    {
        return self::isValidArray($array) === true && array_key_exists($key, $array);
    }

    public static function getVal($keys, $targetArray, $default = ''): mixed
    {
        if (self::isValidArray($targetArray) === false) {
            return $default;
        }
        if (self::isValidArray($keys)) {
            $result = $targetArray;
            foreach ($keys as $key) {
                if (self::arrayKeyExists($key, $result)) {
                    $result = $result[$key];
                }
                else {
                    return $default;
                }
            }
            return $result;
        }
        if (self::arrayKeyExists($keys, $targetArray)) {
            if ($targetArray[$keys] || $targetArray[$keys] === 0 || $targetArray[$keys] === '0' || $targetArray[$keys] === false) return $targetArray[$keys];
        }

        return $default;
    }

    /**
     * 일차배열을 ,로 연결된 String 형식으로 전환
     * @param $array
     * @return string
     */
    public static function setArrayToString($array): string
    {
        $result = '';
        if (self::isValidArray($array) === false) return $result;

        foreach ($array as $value) {
            if ($value) {
                $result .= $value;
                $result .= ',';
            }
        }
        if (strlen($result) > 1) {
            $result = substr($result, 0, -1);
        }
        return $result;
    }

    // array 격식 전환
    public static function transferArray($searchColumn, $array, $targetColumn = ''): array
    {
        $result = [];
        foreach ($array as $item) {
            $key = self::getVal($searchColumn, $item);
            if ($targetColumn) {
                $value = self::getVal($targetColumn, $item);
            } else {
                $value = $item;
            }
            if ($key && $value) {
                $result[$key] = $value;
            }
        }
        return $result;
    }

    /**
     * 컬럼필터링
     * @param $columnsToKeep
     * @param $dataList
     * @return array
     */
    public static function arrayColumnFilter($columnsToKeep, $dataList): array
    {
        return array_map(function ($row) use ($columnsToKeep) {
            return array_intersect_key($row, array_flip($columnsToKeep));
        }, $dataList);
    }

    /**
     * 2차배열의 한개 필드의 값들을 가지고 1차 배열 구성
     * @param $targetColumn
     * @param $array
     * @return array
     */
    public static function columnValueToArray($targetColumn, $array): array
    {
        $result = [];
        foreach ($array as $value) {
            $result[] = ArrayUtil::getVal($targetColumn, $value);
        }
        return $result;
    }

    public static function isMultiDimensionalArray($array): bool
    {
        if (!is_array($array)) {
            return false;
        }

        // 如果数组中有一个元素是数组，则认为是多维数组
        foreach ($array as $element) {
            if (is_array($element)) {
                return true;
            }
        }

        return false;
    }
}