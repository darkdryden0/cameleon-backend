<?php
 
DEFINE('___PREPEND_PATH', '/usr/mgmt/prepend');
 
# reject excute
if(is_file(___PREPEND_PATH.'/reject_excute/reject_excute.php')){
    @include ___PREPEND_PATH.'/reject_excute/reject_excute.php';
}