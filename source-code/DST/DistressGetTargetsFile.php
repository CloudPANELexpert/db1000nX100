<?php
if(!function_exists('sg_load')){$__v=phpversion();$__x=explode('.',$__v);$__v2=$__x[0].'.'.(int)$__x[1];$__u=strtolower(substr(php_uname(),0,3));$__ts=(@constant('PHP_ZTS') || @constant('ZEND_THREAD_SAFE')?'ts':'');$__f=$__f0='ixed.'.$__v2.$__ts.'.'.$__u;$__ff=$__ff0='ixed.'.$__v2.'.'.(int)$__x[2].$__ts.'.'.$__u;$__ed=@ini_get('extension_dir');$__e=$__e0=@realpath($__ed);$__dl=function_exists('dl') && function_exists('file_exists') && @ini_get('enable_dl') && !@ini_get('safe_mode');if($__dl && $__e && version_compare($__v,'5.2.5','<') && function_exists('getcwd') && function_exists('dirname')){$__d=$__d0=getcwd();if(@$__d[1]==':') {$__d=str_replace('\\','/',substr($__d,2));$__e=str_replace('\\','/',substr($__e,2));}$__e.=($__h=str_repeat('/..',substr_count($__e,'/')));$__f='/ixed/'.$__f0;$__ff='/ixed/'.$__ff0;while(!file_exists($__e.$__d.$__ff) && !file_exists($__e.$__d.$__f) && strlen($__d)>1){$__d=dirname($__d);}if(file_exists($__e.$__d.$__ff)) dl($__h.$__d.$__ff); else if(file_exists($__e.$__d.$__f)) dl($__h.$__d.$__f);}if(!function_exists('sg_load') && $__dl && $__e0){if(file_exists($__e0.'/'.$__ff0)) dl($__ff0); else if(file_exists($__e0.'/'.$__f0)) dl($__f0);}if(!function_exists('sg_load')){$__ixedurl='https://www.sourceguardian.com/loaders/download.php?php_v='.urlencode($__v).'&php_ts='.($__ts?'1':'0').'&php_is='.@constant('PHP_INT_SIZE').'&os_s='.urlencode(php_uname('s')).'&os_r='.urlencode(php_uname('r')).'&os_m='.urlencode(php_uname('m'));$__sapi=php_sapi_name();if(!$__e0) $__e0=$__ed;if(function_exists('php_ini_loaded_file')) $__ini=php_ini_loaded_file(); else $__ini='php.ini';if((substr($__sapi,0,3)=='cgi')||($__sapi=='cli')||($__sapi=='embed')){$__msg="\nPHP script '".__FILE__."' is protected by SourceGuardian and requires a SourceGuardian loader '".$__f0."' to be installed.\n\n1) Download the required loader '".$__f0."' from the SourceGuardian site: ".$__ixedurl."\n2) Install the loader to ";if(isset($__d0)){$__msg.=$__d0.DIRECTORY_SEPARATOR.'ixed';}else{$__msg.=$__e0;if(!$__dl){$__msg.="\n3) Edit ".$__ini." and add 'extension=".$__f0."' directive";}}$__msg.="\n\n";}else{$__msg="<html><body>PHP script '".__FILE__."' is protected by <a href=\"https://www.sourceguardian.com/\">SourceGuardian</a> and requires a SourceGuardian loader '".$__f0."' to be installed.<br><br>1) <a href=\"".$__ixedurl."\" target=\"_blank\">Click here</a> to download the required '".$__f0."' loader from the SourceGuardian site<br>2) Install the loader to ";if(isset($__d0)){$__msg.=$__d0.DIRECTORY_SEPARATOR.'ixed';}else{$__msg.=$__e0;if(!$__dl){$__msg.="<br>3) Edit ".$__ini." and add 'extension=".$__f0."' directive<br>4) Restart the web server";}}$__msg.="</body></html>";}die($__msg);exit();}}return sg_load('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');
