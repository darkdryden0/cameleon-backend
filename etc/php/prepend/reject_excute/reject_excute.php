<?php
 
require_once '/usr/mgmt/prepend/reject_excute/util.php';
 
if(PHP_SAPI !== 'cli' and (empty($_SERVER) === false)){
    $__aPHPExecuteConfig = array(
        'config_file' => '/usr/mgmt/prepend/reject_excute/reject_excute.ini',
        'enable' => '',
        'logging' => '',
        'run_exit' => '',
        'log_file' => '',
        'disable_path' => array(),
        'deny_path' => array(),
        'allow_user' => array(),
        'allow_group' => array(),
        'allow_path' => array(),
        'deny_user' => array(),
        'deny_group' => array(),
        'auto_prepend_file' => array(),
        'auto_append_file' => array(),
        'script_file_name' => stripslashes($_SERVER['SCRIPT_FILENAME']),
        'document_root' => stripslashes($_SERVER['DOCUMENT_ROOT']),
        'path_translated' => stripslashes($_SERVER['PATH_TRANSLATED']),
        'disable_flag' => false,
        'script_file_user' => '',
        'script_file_grp' => '',
        'process_user' => 'hosting',
        'deny_path_flag' => false,
        'allow_path_flag' => false,
        'routing_file' => array(),
        'except_ext' => array('js', 'css', 'txt')
    );
 
    if(is_file($__aPHPExecuteConfig['config_file'])){
        $__mConfig = @parse_ini_file($__aPHPExecuteConfig['config_file']);
    }
 
    if(is_array($__mConfig)){
        foreach($__mConfig as $__sKey => $__sValue){
            if(is_array($__sValue)){
                $__sValue = array_filter($__sValue);
                $__sValue = array_map('trim', $__sValue);
            }elseif(is_string($__sValue)){
                $__sValue = trim($__sValue);
                if(is_array($__aPHPExecuteConfig[$__sKey])){
                    $__sValue = explode(',', $__sValue);
                    $__sValue = array_filter($__sValue);
                    $__sValue = array_map('trim', $__sValue);
                }
            }
 
            $__aPHPExecuteConfig[$__sKey] = $__sValue;
        }
        unset($__sKey);
        unset($__sValue);
        unset($__mConfig);
    }
 
    if($__aPHPExecuteConfig['enable'] === '1'){
        if(is_array($__aPHPExecuteConfig['disable_path'])){
            foreach($__aPHPExecuteConfig['disable_path'] as $__sPathPattern){
                $__sPathPattern = __get_path_pattern($__sPathPattern);
                if(preg_match('/^'.$__sPathPattern.'/', $__aPHPExecuteConfig['script_file_name'])){
                    $__aPHPExecuteConfig['disable_flag'] = true;
                }
            }
            unset($__sPathPattern);
        }
 
        if($__aPHPExecuteConfig['disable_flag'] === false){
            if(is_file($__aPHPExecuteConfig['script_file_name'])){
                $__aFileStat = stat($__aPHPExecuteConfig['script_file_name']);
 
                if(in_array($__aPHPExecuteConfig['script_file_name'], $__aPHPExecuteConfig['routing_file']) and is_file($__aPHPExecuteConfig['path_translated'])){
                    $__sExt = pathinfo($__aPHPExecuteConfig['path_translated'], PATHINFO_EXTENSION);
                    if (!in_array($__sExt, $__aPHPExecuteConfig['except_ext'])){
                        $__aFileStat = stat($__aPHPExecuteConfig['path_translated']);
                    }
                }
 
                $__aUidInfo = posix_getpwuid($__aFileStat['uid']);
                if(array_key_exists('name', $__aUidInfo)){
                    $__aPHPExecuteConfig['script_file_user'] = $__aUidInfo['name'];
                }
                $__aGidInfo = posix_getgrgid($__aFileStat['gid']);
                if(array_key_exists('name', $__aGidInfo)){
                    $__aPHPExecuteConfig['script_file_grp'] = $__aGidInfo['name'];
                }
 
                unset($__sExt);
                unset($__aUidInfo);
                unset($__aGidInfo);
                unset($__aFileStat);
            }
 
            $__aProcStat = posix_getpwuid(posix_geteuid());
            if(is_array($__aProcStat)){
                if(array_key_exists('name', $__aProcStat)){
                    $__aPHPExecuteConfig['process_user'] = $__aProcStat['name'];
                }
            }
            unset($__aProcStat);
        }
    }
 
    if($__aPHPExecuteConfig['enable'] === '1'
        and $__aPHPExecuteConfig['disable_flag'] === false
        and $__aPHPExecuteConfig['script_file_user'] !== ''
        and $__aPHPExecuteConfig['script_file_grp'] !== ''
        and $__aPHPExecuteConfig['process_user'] !== ''
    ){
        foreach($__aPHPExecuteConfig['deny_path'] as $__sPathPattern){
            $__sPathPattern = __get_path_pattern($__sPathPattern);
            if(preg_match('/^'.$__sPathPattern.'/', $__aPHPExecuteConfig['script_file_name'])){
                $__aPHPExecuteConfig['deny_path_flag'] = true;
                break;
            }
        }
 
        foreach($__aPHPExecuteConfig['allow_path'] as $__sPathPattern){
            $__sPathPattern = __get_path_pattern($__sPathPattern);
            if(preg_match('/^'.$__sPathPattern.'/', $__aPHPExecuteConfig['script_file_name'])){
                $__aPHPExecuteConfig['allow_path_flag'] = true;
                if(preg_match('/^\/home\/hosting_users\/[\w\/]+\/www$/', $__aPHPExecuteConfig['document_root'])){
                    $__sAbsPath = str_replace("/www","", $__aPHPExecuteConfig['document_root']);
                    $__sAbsPath = str_replace("//", "/", $__sAbsPath);
                    $__sOverrideFile = $__sAbsPath.'/.reject_excute';
                    if(file_exists($__sOverrideFile) and filesize($__sOverrideFile) > 1){
                        $__fp  = fopen($__sOverrideFile, "r");
                        $__sTmp_enable = fread($__fp, filesize($__sOverrideFile));
                        fclose($__fp);
                        $__sTmp_enable = trim($__sTmp_enable);
                        if(strtolower($__sTmp_enable) === 'on'){
                            $__aPHPExecuteConfig['run_exit'] = '1';
                        }else{
                            $__aPHPExecuteConfig['run_exit'] = '';
                        }
                    }else{
                        $__aPHPExecuteConfig['run_exit'] = '';
                    }
                }
 
                unset($__sAbsPath, $__sOverrideFile, $__fp, $__sTmp_enable);
                break;
            }
        }
 
        if($__aPHPExecuteConfig['deny_path_flag'] === true){
            if(!in_array($__aPHPExecuteConfig['script_file_user'], $__aPHPExecuteConfig['allow_user']) and !empty($__aPHPExecuteConfig['allow_user'])){
                if($__aPHPExecuteConfig['logging'] === '1'){
                    __write_log($__aPHPExecuteConfig['log_file'], $__aPHPExecuteConfig['script_file_name'] . ' ' . $__aPHPExecuteConfig['script_file_grp'] . ':'. $__aPHPExecuteConfig['script_file_user'] . ' file\'s user not allow user');
                }
 
                if($__aPHPExecuteConfig['run_exit'] === '1'){
                    __set_header_exit();
                }
            }
 
            if(!in_array($__aPHPExecuteConfig['script_file_grp'], $__aPHPExecuteConfig['allow_group']) and !empty($__aPHPExecuteConfig['allow_group'])){
                if($__aPHPExecuteConfig['logging'] === '1'){
                    __write_log($__aPHPExecuteConfig['log_file'], $__aPHPExecuteConfig['script_file_name'] . ' ' . $__aPHPExecuteConfig['script_file_grp'] . ':'. $__aPHPExecuteConfig['script_file_user'] . ' file\'s user not allow group');
                }
 
                if($__aPHPExecuteConfig['run_exit'] === '1'){
                    __set_header_exit();
                }
            }
        }
 
        if($__aPHPExecuteConfig['allow_path_flag'] === true){
            if(in_array($__aPHPExecuteConfig['script_file_user'], $__aPHPExecuteConfig['deny_user']) and !empty($__aPHPExecuteConfig['deny_user'])){
                if($__aPHPExecuteConfig['logging'] === '1'){
                    __write_log($__aPHPExecuteConfig['log_file'], $__aPHPExecuteConfig['script_file_name'] . ' ' . $__aPHPExecuteConfig['script_file_grp'] . ':'. $__aPHPExecuteConfig['script_file_user'] . ' file\'s user not deny user');
                }
 
                if($__aPHPExecuteConfig['run_exit'] === '1'){
                    __set_header_exit();
                }
            }
 
            if(in_array($__aPHPExecuteConfig['script_file_grp'], $__aPHPExecuteConfig['deny_group']) and !empty($__aPHPExecuteConfig['deny_group'])){
                if($__aPHPExecuteConfig['logging'] === '1'){
                    __write_log($__aPHPExecuteConfig['log_file'], $__aPHPExecuteConfig['script_file_name'] . ' ' . $__aPHPExecuteConfig['script_file_grp'] . ':'. $__aPHPExecuteConfig['script_file_user'] . ' file\'s user not deny group');
                }
 
                if($__aPHPExecuteConfig['run_exit'] === '1'){
                    __set_header_exit();
                }
            }
        }
    }
 
    unset($__aPHPExecuteConfig);
}
?>