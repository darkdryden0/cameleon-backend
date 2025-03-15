<?php
function __get_path_pattern($__sPathPattern){
    $__sTmpPathPattern = '';
    $__mPathPattern = preg_split('//', $__sPathPattern, -1, PREG_SPLIT_NO_EMPTY);
    if(is_array($__mPathPattern)){
        foreach($__mPathPattern as $__sChar){
            if($__sChar === '/'){
                $__sChar = '\/';
            }
            if($__sChar === '*'){
                $__sChar = '[\w\/]+';
            }
            $__sTmpPathPattern .= $__sChar;
        }
    }
    return $__sTmpPathPattern;
}
 
function __write_log($sFile, $sContent){
    $__sDate = date('Y-m-d H:i:s');
    $__sLogContent .= $__sDate;
    $__sLogContent .= ' ';
    $__sLogContent .= $sContent;
    $__sLogContent .= PHP_EOL;
 
    if($__fp = fopen($sFile, "a+")) {
        fwrite($__fp, $__sLogContent, strlen($__sLogContent));
        fclose($__fp);
    }
 
    $__sPerms = substr(sprintf('%o', fileperms($sFile)), -4);
    if ($__sPerms != "0666") {
        chmod($sFile, 0666);
    }
}
 
function __set_header_exit($sHearder=403){
    if($sHearder == 403){
        header('HTTP/1.1 403 Forbidden by phpexecute');
    }
    exit();
}
?>