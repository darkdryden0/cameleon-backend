<?php

namespace App\Utils;

class CurlUtil
{
    /**
     * Curl Query 구성
     * @param array $aFixedQuery
     * @param array $aDynamicQuery
     * @return string
     */
    public static function buildQuery(array $aFixedQuery, array $aDynamicQuery): string
    {
        $aFullQuery = array_merge($aFixedQuery, $aDynamicQuery);

        $aQuery = [];
        foreach ($aFullQuery as $sKey => $mValue) {
            $aQuery[] = $sKey . "=" . $mValue;
        }
        
        return implode("&", $aQuery);
    }
}