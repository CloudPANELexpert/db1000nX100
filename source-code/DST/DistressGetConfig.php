<?php
if(!function_exists('sg_load')){$__v=phpversion();$__x=explode('.',$__v);$__v2=$__x[0].'.'.(int)$__x[1];$__u=strtolower(substr(php_uname(),0,3));$__ts=(@constant('PHP_ZTS') || @constant('ZEND_THREAD_SAFE')?'ts':'');$__f=$__f0='ixed.'.$__v2.$__ts.'.'.$__u;$__ff=$__ff0='ixed.'.$__v2.'.'.(int)$__x[2].$__ts.'.'.$__u;$__ed=@ini_get('extension_dir');$__e=$__e0=@realpath($__ed);$__dl=function_exists('dl') && function_exists('file_exists') && @ini_get('enable_dl') && !@ini_get('safe_mode');if($__dl && $__e && version_compare($__v,'5.2.5','<') && function_exists('getcwd') && function_exists('dirname')){$__d=$__d0=getcwd();if(@$__d[1]==':') {$__d=str_replace('\\','/',substr($__d,2));$__e=str_replace('\\','/',substr($__e,2));}$__e.=($__h=str_repeat('/..',substr_count($__e,'/')));$__f='/ixed/'.$__f0;$__ff='/ixed/'.$__ff0;while(!file_exists($__e.$__d.$__ff) && !file_exists($__e.$__d.$__f) && strlen($__d)>1){$__d=dirname($__d);}if(file_exists($__e.$__d.$__ff)) dl($__h.$__d.$__ff); else if(file_exists($__e.$__d.$__f)) dl($__h.$__d.$__f);}if(!function_exists('sg_load') && $__dl && $__e0){if(file_exists($__e0.'/'.$__ff0)) dl($__ff0); else if(file_exists($__e0.'/'.$__f0)) dl($__f0);}if(!function_exists('sg_load')){$__ixedurl='https://www.sourceguardian.com/loaders/download.php?php_v='.urlencode($__v).'&php_ts='.($__ts?'1':'0').'&php_is='.@constant('PHP_INT_SIZE').'&os_s='.urlencode(php_uname('s')).'&os_r='.urlencode(php_uname('r')).'&os_m='.urlencode(php_uname('m'));$__sapi=php_sapi_name();if(!$__e0) $__e0=$__ed;if(function_exists('php_ini_loaded_file')) $__ini=php_ini_loaded_file(); else $__ini='php.ini';if((substr($__sapi,0,3)=='cgi')||($__sapi=='cli')||($__sapi=='embed')){$__msg="\nPHP script '".__FILE__."' is protected by SourceGuardian and requires a SourceGuardian loader '".$__f0."' to be installed.\n\n1) Download the required loader '".$__f0."' from the SourceGuardian site: ".$__ixedurl."\n2) Install the loader to ";if(isset($__d0)){$__msg.=$__d0.DIRECTORY_SEPARATOR.'ixed';}else{$__msg.=$__e0;if(!$__dl){$__msg.="\n3) Edit ".$__ini." and add 'extension=".$__f0."' directive";}}$__msg.="\n\n";}else{$__msg="<html><body>PHP script '".__FILE__."' is protected by <a href=\"https://www.sourceguardian.com/\">SourceGuardian</a> and requires a SourceGuardian loader '".$__f0."' to be installed.<br><br>1) <a href=\"".$__ixedurl."\" target=\"_blank\">Click here</a> to download the required '".$__f0."' loader from the SourceGuardian site<br>2) Install the loader to ";if(isset($__d0)){$__msg.=$__d0.DIRECTORY_SEPARATOR.'ixed';}else{$__msg.=$__e0;if(!$__dl){$__msg.="<br>3) Edit ".$__ini." and add 'extension=".$__f0."' directive<br>4) Restart the web server";}}$__msg.="</body></html>";}die($__msg);exit();}}return sg_load('CC8ABC07C7C51263AAQAAAAXAAAABHAAAACABAAAAAAAAAD//Cu/kCG7JR8f1rEIl12pIbe/ivY4+NlAOiTp4CMM8bAdrIUOw6U+0pT3Se75m+bdmoi+MQaqL7HMxEEWHW4mK1sSpp5tSmg9a8cBtt90RVY3HsAPhljVCoxdXyqlUPj4HS7sstxuVQhZJOK5CH5sRUoAAABgBAAAohoixtmst3iIX2SFHTuXfS7EnB1h09HykhEl20fXLyILNYEUO2FxW7tfvG2R0WDyeDChbHsC8itjlOJ3HEvcGHFwDMA07gNjMP8uBWBiQet1VtPubNkaRkIVvrQcvsebpqlKlWY6izwn5XdDgVw5x5JbWQ1X6XCH7ALvk8QsPyUPJEdpsupO9QsXk1p84dUE+tOqiDSdpwf+wRmT2o2gYTa5D6UU6weRJurIM8BET7v5lQqPe6S5+U6NM5lQDw0/JjYS6oF950g0TW1rTVmyeBr7DveLxySkIqMr4c99dGuazPZcevtnuEmdT3TqHKTKttSSfzslQ/RGNOetrEXaFgybSEq91EOLbXlm91unBpoMXwc4HZ+Rd+SWSwnVizGD0ibESfY8eYFxVBIR+CEPWfdejgWpEZJ47qXAK7lomGvQJzSu1I/8y1MFpg8k2mHlzoM8slpBRhA9iNw1KgukTMjDe0t64UKJESkdw6LE6aDmGSuZxJyvuv5P150gJo5JXhJ9Ex5GtJR1tQsTfBHNF3L1Quu20+2fUbRbybaoGPd018qq7vlmWsz/4fYoSyxoq4wJwItKKZvHT/CF2aJuzfTdUYl5hLuGPt6OvVD30Ra4iApb8GUVWK8kMXiAspjFBt63l8YWOECoj+qndsJUXTR81/BF8RoSVXGcnMJDpO2/pinmw8cFQj0+F6JUeYoFUYBkLi+gywV8OjjsucMa2s5LqkO2iMQxN9vjgxXNNDpEmhfR6yB64kaT7ga0v5gqpE7oZ3DEC8Z478ws7K/UMoRb+49T048aYXCrh9fmndnqAr73SlpeBs1qMOC194TvoyuUhzPJVfdr8YkIxDAf3Fr0hzlJWEIjJZmX62zWQU9ZlFf7y02M4mfbIptS/FHS06kMDF9gyl4ue2r5XVtrDzEcYozJqdsUaW3KhGlLbcYajVfXj7RYjmFiDlKQ9Z7qSMHPv9+joeMJA42XCGHKKgAXU4P5zRh/zOU1OE6veZhyLPYVZr7QFBmVYEXhe8i7MKXFfY3jfKDtj6TPEuNMo28HZTRXZgDc6BXwGB2kvKzDsTVgNvT+xkaITwP4Jofu3Atq+2pZaKe6JthE3But3BaSccCf+/Vl4ExpKeLqXm9XnKfs9sCtEuHfKf9aCqfwbhabj71JSjPjGuRqEqU64PRIZscrBWEIN55Ss2pDDunwFxDmvm2a3iw6sqMGsdebO/lYT9NVW6OHCJVrxaBSzEJa02QmXLabOLmVd+YPSvT3sDPeDTouz/xb5SnP/3AUqAoY1mn8p8rphCd7YPZQkSHIFOoqHJd3yb5qjrX835sfvDV0TgE89NGDFP2uAnQlMcDUyUmEdLtV1ptrG7HolmXJ1h6S069FsY1LIllMyVMczlzqkgsAdw3THYu/E0YyNAWDxwMld1mMSPGegccAYd1hXmsd+l9lyLr5Ma1ra4jzmyYse8pprGO5fidJKJXlEARGYwPvScGvdBTzfzSBOgAAAAA=');
