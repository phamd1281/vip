<meta http-equiv='refresh' content='2'>
<html>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<head>
		<title>Nothing at here ...</title>
	</head>
	<body>
<!-- xml version="1.0" encoding="utf-8" -->
<?php
	/* Include */
	include_once('config.php');
	include_once('functions.php');
	echo 'Cbox: ' . $config['cboxUrl'] . '&sec=main' . '<br>';
	$page = file_get_contents($config['cboxUrl'] . "&sec=main");
	$line = explode('<tr id=', $page);
	$lineMax = 8;

	/* Clear ids post */
	if (!file_exists($fileList['time']))
		_writeFile($fileList['time'], time(), 'w');
	$timeLast = _readFile($fileList['time']);
	if (time() - $timeLast >= 3600 /* 1 Hour */) {
		$ids = _readFile($fileList['post']);
		if ($ids != '') { 
			$ids = explode('|', $ids);
			if (count($ids) <= $lineMax)
				$i = 0;
			else
				$i = count($ids) - $lineMax;
			$string = '';
			for ($i = $i; $i < count($ids) - 1; $i++)
				$string .= $ids[$i] . '|';
			/* Write new ids */
			_writeFile($fileList['post'], $string, 'w');
			/* Write new time */
			_writeFile($fileList['time'], time(), 'w');
		}
	}

	for ($i = 2; $i < $lineMax; $i++) {
		/* Get something need :)) */
		$infoUser = array();
		preg_match('/<b class="(.*?)">(.*?)<\/b>/s', $line[$i], $matches);
		if (strcmp($matches[2], '<a class="__cf_email__"') == 0) {
			if (preg_match('/data-cfemail="(.*?)">\[email&nbsp;protected]<\/a>/i', $matches[2], $temp))
				$infoUser['name'] = deCFEmail($temp[1]);
		} 
		else
			$infoUser['name'] = $matches[2];
		$infoUser['user'] = strtolower(htmlspecialchars_decode($infoUser['name']));

		preg_match('/<b class="(.*?)">(.*?)<\/b>/s', $line[$i], $matches);
		$infoUser['rank'] = $matches[1]; 
		/* PRIMARY */ 
		if (($config['work'] == true) && ($infoUser['user'] != strtolower($config['bot']['name']))) {
			/* Get message */
			preg_match('/<\/b>:(.*)/', $line[$i], $matches);
			$message = $matches[1];
			if (preg_match('/<a class="autoLink" href="(.*?)"/i', $message, $matches) && !preg_match('/\[media](.*)\[\/media]/', $line[$i])) {  
				$urlOriginal = $matches[1];
				/* Check user */
				if (stristr($infoUser['rank'], 'nme pn_reg') && !in_array($infoUser['name'], $adminList) && !in_array($infoUser['user'], $vipList) && !in_array($infoUser['name'], $blackList)) {	
					/* Path to user file */
					$userFile = "user/" . md5($infoUser['user']) . ".dat";
					if (!file_exists($userFile)) {
						/* Get info from user */
						if (preg_match('%(sent from (.*)[^(])<\/sub>%U', $line[$i], $matches)) 
							$infoUser['country'] = ($matches[2] != '' ? ($matches[2] == "Vietnam" ? "vn" : "us") : "us");
						else
							$infoUser['country'] = "us";
						/* Create new data user */
						$userData = array(
							'infoUser' => array(
								'name' => $infoUser['name'], 
								'user' => $infoUser['user'], 
								'rank' => 'member', 
								'country' => $infoUser['country']
							), 
							'time' => array(
								'leech' => 0,
								'minute' => 0,
								'second' => 0
							),
							'bandwith' => array(
								'status' => false
							), 
							'infoUrl' => array(),
							'leech' => array(
								'status' => false
							),
							'expired' => date('d/m/Y')
						);
					}
					else {
						$userData = json_decode(_readFile($userFile), true);
						/* Check data of user */
						if ($userData['expired'] != date('d/m/Y')) {
							/* Reset and update data of user */
							$userData['infoUser']['rank'] = 'member';
							$userData['bandwith'] = array('status' => false);
							$userData['infoUrl'] = array();
							$userData['leech'] = array('status' => false);
							$userData['expired'] = date('d/m/Y');
						}
					}
					/* Update data for user (important) */
					preg_match('%"(.*)"><td class%U', $line[$i], $matches);
					$userData['infoUser']['id'] = $matches[1];
					$userData['bandwith']['status'] = false;
					/* Remove $userInfo */
					unset($infoUser);
					/* Check post */ 
					if (!_checkPost($fileList['post'], $userData['infoUser']['id'], $lineMax)) {
						/* Save data */
						_writeFile($fileList['post'], $userData['infoUser']['id'] . '|', 'a');
						/* Check info user */ 
						if (!isset($userData['infoUser']['name']) || $userData['infoUser']['name'] == '' || !isset($userData['infoUser']['user']) || $userData['infoUser']['user'] == '' || !isset($userData['infoUser']['country']) || $userData['infoUser']['country'] == '') {
							/* Get name, user */
							preg_match('/<b class="(.*?)">(.*?)<\/b>/s', $line[$i], $matches);
							if (strcmp($matches[2], '<a class="__cf_email__"') == 0) {
								if (preg_match('/data-cfemail="(.*?)">\[email&nbsp;protected]<\/a>/i', $matches[2], $temp))
									$userData['infoUser']['name'] = deCFEmail($temp[1]);
							} 
							else
								$userData['infoUser']['name'] = $matches[2];
							$userData['infoUser']['user'] = strtolower($userData['infoUser']['name']);
							/* Get rank */
							$userData['infoUser']['rank'] = "member";
							/* Get country */
							if (preg_match('%(sent from (.*)[^(])<\/sub>%U', $line[$i], $matches)) 
								$userData['infoUser']['country'] = ($matches[2] != '' ? ($matches[2] == "Vietnam" ? "vn" : "us") : "us");
							else
								$userData['infoUser']['country'] = "us";
							/* Check again */
							if (!isset($userData['infoUser']['name']) || $userData['infoUser']['name'] == '' || !isset($userData['infoUser']['user']) || $userData['infoUser']['user'] == '' || !isset($userData['infoUser']['country']) || $userData['infoUser']['country'] == '') {
								/* Step loop */
								continue; 
							}
						}
						/* Convert link */
						$userData['infoUrl']['url'] = str_replace('//ul.to', '//uploaded.net/file', $urlOriginal);
						$userData['infoUrl']['url'] = str_replace('//uploaded.to', '//uploaded.net', $userData['infoUrl']['url']);
						$userData['infoUrl']['url'] = str_replace('//dfiles.eu', '//depositfiles.com', $userData['infoUrl']['url']);
						$userData['infoUrl']['url'] = str_replace('//depositfile.org', '//depositfile.com', $userData['infoUrl']['url']);
						$userData['infoUrl']['url'] = str_replace('//filesflash.net', '//filesflash.com', $userData['infoUrl']['url']);
						$userData['infoUrl']['url'] = str_replace('//rg.to', '//rapidgator.net', $userData['infoUrl']['url']);
						$userData['infoUrl']['url'] = str_replace('yunfile.com', 'dfpan.com', $userData['infoUrl']['url']);
						$userData['infoUrl']['url'] = str_replace(array('//k2s.cc', '//keep2s.cc'), '//keep2share.cc', $userData['infoUrl']['url']);
						if (preg_match('/\?(.*?)=/i', $userData['infoUrl']['url']))
							$userData['infoUrl']['url'] = substr($userData['infoUrl']['url'], 0, strpos($userData['infoUrl']['url'], '?'));
						$userData['infoUrl']['url'] = str_replace('%7C', '|', $userData['infoUrl']['url']);	
						/* Get Domain */
						if (stristr($userData['infoUrl']['url'], 'dfpan.com'))	
							$hostOriginal = 'yunfile.com';
						else if (stristr($userData['infoUrl']['url'], 'isra.cloud'))
							$hostOriginal = 'isra.cloud';
						else	
							$hostOriginal = _getDomain($userData['infoUrl']['url']);
						/* Check host suport */
						if (in_array($hostOriginal, $hostOnline)) {
							/* Get info link */
							if (_getInfoLink($userData['infoUrl'], $userData['infoUrl']['url'])) {
								$notice = $noticeList['getInfoLink'][$userData['infoUrl']['status']][$userData['infoUser']['country']];
								/* Post message */
								_postMessage($notice, $userData['infoUser']); 
								continue;
							}
							/* Exception */
							if (stristr($userData['infoUrl']['url'], '4share.vn')) {
								$page = file_get_contents($userData['infoUrl']['url']);
								if (stristr($page, "chứa file này đang bảo dưỡng!")) {
									preg_match('/<b>(.*?)<\/b><\/div>/', $page, $matches);
									if ($userData['infoUser']['country'] == "vn")
										$notice = "[center][b][color=green]Host[/color] [den]" . $hostOriginal . "[/mau] [color=green] thông báo:[/color] [color=red]" . trim($matches[1]) . "[/color][/b][/center]";
									else
										$notice = "[center][b][color=green]Host[/color] [den]" . $hostOriginal . "[/mau] [color=green] said:[/color] [color=red]" . trim($matches[1]) . "[/color][/b][/center]";
									/* Post message */
									_postMessage($notice, $userData['infoUser']); 
									continue;								
								}
							}							
							/* Check past link */
							if ($userData['leech']['status']) {
								if (($userData['leech']['url'] == $userData['infoUrl']['url']) && (time() - $userData['leech']['expired']) <= 60) {
									/* Generate bbCode */
									$styleList = array(
										'title' => $baseStyleList['title'][array_rand($baseStyleList['title'], 1)],
										'fileName' => $baseStyleList['fileName'][array_rand($baseStyleList['fileName'], 1)],
										'fileSize' => $baseStyleList['fileSize'][array_rand($baseStyleList['fileSize'], 1)] 
									);					
									$bbCode = "[center][b][img]http://i1157.photobucket.com/albums/p581/10steamxxx/smile10s/dl3.gif[/img][br][url=" . $userData['leech']['urlDownload'] . "][" . $styleList['title'] . "]VNZ.TEAM[/mau] | [img]https://www.google.com/s2/favicons?domain=" . $hostOriginal . "[/img] [color=" . $styleList['fileName'] . "]" . $userData['leech']['fileName'] . "[/color] [color=" . $styleList['fileSize'] . "](" . $userData['leech']['fileSize'] . ")[/color][/url][/b][/center]";
									/* Post message */
									_postMessage($bbCode, $userData['infoUser']);
									/* Step loop */
									continue;
								}
							}
							/* Check time post */
							if (_checkTimePost($userData['time'], $config['linkPerTime']) && ($config['linkPerTime'] > 0)) {
								if ($userData['time']['minute'] == 0) {
									$notice = str_replace("time_second", $userData['time']['second'], $noticeList['timeLimited']['second'][$userData['infoUser']['country']]);
									/* Post message */
									_postMessage($notice, $userData['infoUser']);
									/* Step loop */
									continue; 
								}
								else
									if ($userData['time']['second'] == 0) {
										$notice = str_replace("time_minute", $userData['time']['minute'], $noticeList['timeLimited']['minute'][$userData['infoUser']['country']]);
										/* Post message */
										_postMessage($notice, $userData['infoUser']);
										/* Step loop */
										continue; 
									}
									else {
										$notice = str_replace("time_minute", $userData['time']['minute'], $noticeList['timeLimited']['min_sec'][$userData['infoUser']['country']]);
										$notice = str_replace("time_second", $userData['time']['second'], $notice);
										/* Post message */
										_postMessage($notice, $userData['infoUser']);
										/* Step loop */
										continue; 
									}
							}
							/* Check host limited */
							if (in_array($hostOriginal, $config['hostLimited'])) {
								$notice = str_replace("icon_host", "https://www.google.com/s2/favicons?domain=" . $hostOriginal, $noticeList['hostLimited'][$userData['infoUser']['country']]);
								$notice = str_replace("name_host", ucfirst($hostOriginal), $notice);
								/* Post message */
								_postMessage($notice, $userData['infoUser']); 
								/* Step loop */
								continue;
							}
							/* Check size limit */
							if (isset($config['sizeLimited']['member'][$hostOriginal]) && ($config['sizeLimited']['member'][$hostOriginal] > 0)) {
								if ($userData['infoUrl']['realSize'] > $config['sizeLimited']['member'][$hostOriginal]) {
									$notice = str_replace("icon_host", "https://www.google.com/s2/favicons?domain=" . $hostOriginal, $noticeList['sizeLimited']['member'][$userData['infoUser']['country']]);
									$notice = str_replace("name_host", ucfirst($hostOriginal), $notice);
									$notice = str_replace("file_size", _reconvertSize($config['sizeLimited']['member'][$hostOriginal]), $notice);
									/* Post message */
									_postMessage($notice, $userData['infoUser']); 
									/* Step loop */
									continue;
								}
							}
							else {
								if (stristr($hostOriginal, ".vn") && ($config['sizeLimited']['member']['default']['vn'] > 0)) {
									if ($userData['infoUrl']['realSize'] > $config['sizeLimited']['member']['default']['vn']) {
										$notice = str_replace("icon_host", "https://www.google.com/s2/favicons?domain=" . $hostOriginal, $noticeList['sizeLimited']['member'][$userData['infoUser']['country']]);
										$notice = str_replace("name_host", ucfirst($hostOriginal), $notice);
										$notice = str_replace("file_size", _reconvertSize($config['sizeLimited']['member']['default']['vn']), $notice);
										/* Post message */
										_postMessage($notice, $userData['infoUser']);
										/* Step loop */
										continue;
									}
								}
								else if (!stristr($hostOriginal, ".vn") && ($config['sizeLimited']['member']['default']['us'] > 0)) {
									if ($userData['infoUrl']['realSize'] > $config['sizeLimited']['member']['default']['us']) {
										$notice = str_replace("icon_host", "https://www.google.com/s2/favicons?domain=" . $hostOriginal, $noticeList['sizeLimited']['member'][$userData['infoUser']['country']]);
										$notice = str_replace("name_host", ucfirst($hostOriginal), $notice);
										$notice = str_replace("file_size", _reconvertSize($config['sizeLimited']['member']['default']['us']), $notice);
										/* Post message */
										_postMessage($notice, $userData['infoUser']);
										/* Loop */
										continue;
									}
								}
							}
							/* Check bandwith */
							if (isset($config['bandwithLimited']['member'][$hostOriginal]) && ($config['bandwithLimited']['member'][$hostOriginal] > 0)) {
								$remain = (strtotime(str_replace('/', '-', $userData['expired'])) + 86400) - time();
								if (!isset($userData['bandwith'][$hostOriginal]))
									$userData['bandwith'][$hostOriginal] = array();
								if ($config['bandwithLimited']['member']['default'] > 0) {
									if (_checkBandwith($userData['bandwith']['default'], $userData['infoUrl']['realSize'], $config['bandwithLimited']['member']['default'])) {
										if ($userData['bandwith']['default']['left'] - $userData['bandwith']['default']['used'] <= (5 * 1024 * 1024) && $userData['bandwith']['default']['used'] != 0) {
											$notice = str_replace("file_size", _reconvertSize($config['bandwithLimited']['member']['default']), $noticeList['bandwithLimited']['member']['reach'][$userData['infoUser']['country']]);
											$notice = str_replace("icon_host", "http://vnz-leech.com/favicon.ico", $notice);
											$notice = str_replace("name_host", "VNZ-Leech", $notice);
											$notice = str_replace("time", "[cam]" . floor(($remain % 86400) / 3600) . " hour(s)[/mau] [tim]" . floor((($remain % 86400) % 3600) / 60) . " minute(s)[/mau] [den]" . floor((($remain % 86400) % 3600) % 60) ." second(s)[/mau]", $notice);
										}
										else {
											$notice = str_replace("file_size", _reconvertSize($userData['bandwith']['default']['left'] - $userData['bandwith']['default']['used']), $noticeList['bandwithLimited']['member']['near'][$userData['infoUser']['country']]);
											$notice = str_replace("icon_host", "http://vnz-leech.com/favicon.ico", $notice);
											$notice = str_replace("name_host", "VNZ-Leech", $notice);
											$notice = str_replace("time", "[cam]" . floor(($remain % 86400) / 3600) . " hour(s)[/mau] [tim]" . floor((($remain % 86400) % 3600) / 60) . " minute(s)[/mau] [den]" . floor((($remain % 86400) % 3600) % 60) ." second(s)[/mau]", $notice);
										}										
										/* Post message */
										_postMessage($notice, $userData['infoUser']);
										/* Step loop */
										continue;
									}
								}
								if (_checkBandwith($userData['bandwith'][$hostOriginal], $userData['infoUrl']['realSize'], $config['bandwithLimited']['member'][$hostOriginal])) {
									if ($userData['bandwith'][$hostOriginal]['left'] - $userData['bandwith'][$hostOriginal]['used'] <= (1 * 1024 * 1024) && $userData['bandwith'][$hostOriginal]['used'] != 0) {
										$notice = str_replace("file_size", _reconvertSize($config['bandwithLimited']['member'][$hostOriginal]), $noticeList['bandwithLimited']['member']['reach'][$userData['infoUser']['country']]);
										$notice = str_replace("icon_host", "https://www.google.com/s2/favicons?domain=" . $hostOriginal, $notice);
										$notice = str_replace("name_host", ucfirst($hostOriginal), $notice);
										$notice = str_replace("time", "[cam]" . floor(($remain % 86400) / 3600) . " hour(s)[/mau] [tim]" . floor((($remain % 86400) % 3600) / 60) . " minute(s)[/mau] [den]" . floor((($remain % 86400) % 3600) % 60) ." second(s)[/mau]", $notice);
									}
									else {
										$notice = str_replace("file_size", _reconvertSize($userData['bandwith'][$hostOriginal]['left'] - $userData['bandwith'][$hostOriginal]['used']), $noticeList['bandwithLimited']['member']['near'][$userData['infoUser']['country']]);
										$notice = str_replace("icon_host", "https://www.google.com/s2/favicons?domain=" . $hostOriginal, $notice);
										$notice = str_replace("name_host", ucfirst($hostOriginal), $notice);
										$notice = str_replace("time", "[cam]" . floor(($remain % 86400) / 3600) . " hour(s)[/mau] [tim]" . floor((($remain % 86400) % 3600) / 60) . " minute(s)[/mau] [den]" . floor((($remain % 86400) % 3600) % 60) ." second(s)[/mau]", $notice);
									}
									/* Post message */
									_postMessage($notice, $userData['infoUser']); 
									/* Step loop */
									continue;
								}	
								/* Save data */								
								$userData['bandwith']['status'] = $hostOriginal;
							}
							else if ($config['bandwithLimited']['member']['default'] > 0) {
								$remain = (strtotime(str_replace('/', '-', $userData['expired'])) + 86400) - time();
								if (_checkBandwith($userData['bandwith']['default'], $userData['infoUrl']['realSize'], $config['bandwithLimited']['member']['default'])) {
									if ($userData['bandwith']['default']['left'] - $userData['bandwith']['default']['used'] <= (1 * 1024 * 1024) && $userData['bandwith']['default']['used'] != 0) {
										$notice = str_replace("file_size", _reconvertSize($config['bandwithLimited']['member']['default']), $noticeList['bandwithLimited']['member']['reach'][$userData['infoUser']['country']]);
										$notice = str_replace("icon_host", "http://vnz-leech.com/favicon.ico", $notice);
										$notice = str_replace("name_host", "VNZ-Leech", $notice);
										$notice = str_replace("time", "[cam]" . floor(($remain % 86400) / 3600) . " hour(s)[/mau] [tim]" . floor((($remain % 86400) % 3600) / 60) . " minute(s)[/mau] [den]" . floor((($remain % 86400) % 3600) % 60) ." second(s)[/mau]", $notice);
									}
									else {
										$notice = str_replace("file_size", _reconvertSize($userData['bandwith']['default']['left'] - $userData['bandwith']['default']['used']), $noticeList['bandwithLimited']['member']['near'][$userData['infoUser']['country']]);
										$notice = str_replace("icon_host", "http://vnz-leech.com/favicon.ico", $notice);
										$notice = str_replace("name_host", "VNZ-Leech", $notice);
										$notice = str_replace("time", "[cam]" . floor(($remain % 86400) / 3600) . " hour(s)[/mau] [tim]" . floor((($remain % 86400) % 3600) / 60) . " minute(s)[/mau] [den]" . floor((($remain % 86400) % 3600) % 60) ." second(s)[/mau]", $notice);
									}	
									/* Post message */
									_postMessage($notice, $userData['infoUser']);
									/* Step loop */
									continue;
								}
								/* Save data */								
								$userData['bandwith']['status'] = "default";
							}
							/* Check 3x */
							if ($config['check3x'] == true) {
								if (_check3x($userData['infoUrl']['url']))
									/* Step loop */ 
									continue;
							}
							/* Check good link */
							foreach ($config['parameterCheck'] as $goodLink) 
								if (!preg_match("/{$goodLink}/", $message, $matches)) {
									/* Post message */
									_postMessage($noticeList['checkLink'][$userData['infoUser']['country']], $userData['infoUser']);
									/* Step loop */
									continue(2);
								}
							/* Get link */
							if (_getLink($userData['leech'], $server[$hostOriginal]['server'], $userData['infoUser']['rank'], $userData['infoUrl']['url'])) {
								/* Save data */
								$userData['time']['leech'] = time();
								if ($userData['bandwith']['status'] != false) {
									if ($userData['bandwith']['status'] != "default") {
										$userData['bandwith'][$userData['bandwith']['status']]['used'] = $userData['bandwith'][$userData['bandwith']['status']]['used'] + $userData['leech']['realSize'];
										$userData['bandwith'][$userData['bandwith']['status']]['left'] = $config['bandwithLimited']['member'][$userData['bandwith']['status']] - $userData['bandwith'][$userData['bandwith']['status']]['used'];							
									}
									else {
										$userData['bandwith'][$userData['bandwith']['status']]['used'] = $userData['bandwith'][$userData['bandwith']['status']]['used'] + $userData['leech']['realSize'];
										$userData['bandwith'][$userData['bandwith']['status']]['left'] = $config['bandwithLimited']['member'][$userData['bandwith']['status']] - $userData['bandwith'][$userData['bandwith']['status']]['used'];
						
										$userData['bandwith']['default']['used'] = $userData['bandwith']['default']['used'] + $userData['leech']['realSize'];
										$userData['bandwith']['default']['left'] = $config['bandwithLimited']['member']['default'] - $userData['bandwith']['default']['used'];							
									}
								}
								/* Generate bbcode */
								$styleList = array(
									'title' => $baseStyleList['title'][array_rand($baseStyleList['title'], 1)],
									'fileName' => $baseStyleList['fileName'][array_rand($baseStyleList['fileName'], 1)],
									'fileSize' => $baseStyleList['fileSize'][array_rand($baseStyleList['fileSize'], 1)] 
								);								
								$bbCode = "[center][b][img]http://i1157.photobucket.com/albums/p581/10steamxxx/smile10s/dl3.gif[/img][br][url=" . $userData['leech']['urlDownload'] . "][" . $styleList['title'] . "]VNZ.TEAM[/mau] | [img]https://www.google.com/s2/favicons?domain=" . $hostOriginal . "[/img] [color=" . $styleList['fileName'] . "]" . $userData['leech']['fileName'] . "[/color] [color=" . $styleList['fileSize'] . "](" . $userData['leech']['fileSize'] . ")[/color][/url]";
								if ($userData['bandwith']['status'] != false) 
									$bbCode .= "[br]([color=green]" . $noticeList['usedBandwith'][$userData['infoUser']['country']] . ":[/color] [color=red]" . _reconvertSize($userData['bandwith'][$userData['bandwith']['status']]['used']) . "[/color] | [color=green]" . $noticeList['leftBandwith'][$userData['infoUser']['country']] . ": [/color] [color=red]" . _reconvertSize($userData['bandwith'][$userData['bandwith']['status']]['left']) . "[/color])[/b][/center]";
								else
									$bbCode .= "[/b][/center]";
								/* Post message */
								_postMessage($bbCode, $userData['infoUser']);
								/* Write data */
								_writeFile($userFile, json_encode($userData), 'w');	
								/* Step loop */
								continue;
							}
							else {
								if (count($server[$hostOriginal]['server']) > 1) {
									$notice = str_replace("file_name", $userData['infoUrl']['fileName'], $noticeList['waitLeech'][$userData['infoUser']['country']]);
									$notice = str_replace("file_size", $userData['infoUrl']['fileSize'], $notice);
									$notice = str_replace("error_status", $userData['leech']['error'], $notice);
									$notice = str_replace("server_leech", str_ireplace('www.', '', parse_url($server[$hostOriginal]['server'][$userData['leech']['server']], PHP_URL_HOST)), $notice);
									$notice = str_replace("icon_host" , "https://www.google.com/s2/favicons?domain=" . $hostOriginal, $notice);
									/* Post message */
									_postMessage($notice, $userData['infoUser']);
									/* Write data */
									_writeFile($userFile, json_encode($userData), 'w');										
									/* Write log */
									$errorLog = PHP_EOL . date('[Y-m-d H:i:s]') . ' ' . '[' . str_ireplace('www.', '', parse_url($server[$hostOriginal]['server'][$userData['leech']['server']], PHP_URL_HOST)) . ']' . ' ' . $userData['leech']['error'];
									_writeFile('log/serverLog.dat', $errorLog, 'a');
									/* Request */
									if (substr(php_uname(), 0, 7) == "Windows")
										execInBackground("/D C:\wget\ wget " . $currentUrl . "/reget.php --post-data=\"user=" . $userData['infoUser']['user'] . "&secure=" . md5($configCpanel['password']) . "\" --output-document=" . getcwd() . "\\temp.txt");
									else 
										execInBackground("wget " . $currentUrl . "/reget.php --post-data=\"user=" . $userData['infoUser']['user'] . "&secure=" . md5($configCpanel['password']) . "\" --output-document=" . getcwd() . "\\temp.txt");
									/* Step loop */
									continue;
								}
								else {
									$notice = str_replace("file_name", $userData['infoUrl']['fileName'], $noticeList['errorLeech'][$userData['infoUser']['country']]);
									$notice = str_replace("file_size", $userData['infoUrl']['fileSize'], $notice);
									$notice = str_replace("error_status", $userData['leech']['error'], $notice);
									$notice = str_replace("server_leech", str_replace('www.', '', parse_url(explode('|', $server[$hostOriginal]['server'][$userData['leech']['server']])[0], PHP_URL_HOST)), $notice);
									$notice = str_replace("icon_host" , "https://www.google.com/s2/favicons?domain=" . $hostOriginal, $notice);
									/* Post message */
									_postMessage($notice, $userData['infoUser']);
									/* Write log */
									$errorLog = PHP_EOL . date('[Y-m-d H:i:s]') . ' ' . '[' . str_replace('www.', '', parse_url(explode('|', $server[$hostOriginal]['server'][$userData['leech']['server']])[0], PHP_URL_HOST)) . ']' . ' ' . $userData['leech']['error'];
									_writeFile('log/serverLog.dat', $errorLog, 'a');	
									/* Step loop */
									continue;
								}
							}
						}
						elseif (in_array($hostOriginal, $hostOffline)) {
							$notice = str_replace("icon_host", "https://www.google.com/s2/favicons?domain=" . $hostOriginal, $noticeList['hostOffline'][$userData['infoUser']['country']]);
							$notice = str_replace("name_host", ucfirst($hostOriginal), $notice);
							/* Post message */
							_postMessage($notice, $userData['infoUser']);
							/* Step loop */
							continue;
						}
						else { 
							/* Old message 
								$notice = str_replace("icon_host", "https://www.google.com/s2/favicons?domain=" . $hostOriginal, $noticeList['notSupport'][$userData['infoUser']['country']]);
								$notice = str_replace("name_host", ucfirst($hostOriginal), $notice);
							*/
							$memberHost = ''; $i = 0;
							foreach ($hostOnline as $host) {
								if (!in_array($host, $config['hostLimited'])) {
									$memberHost .= "[color=#CD0000]" . ucfirst($host) . "[/color]";
									if ($i % 6 == 0 && $i != 0)
							        	$memberHost .= "[br]";
							    	else
							        	$memberHost .= ", ";
							        $i++;
								}
							}
							$vipHost = '';
							for ($i = 0; $i < count($config['hostLimited']); $i++) {
								$vipHost .= "[color=#CD0000]" . ucfirst($config['hostLimited'][$i]) . "[/color]";
								if ($i % 6 == 0 && $i != 0)
						        	$vipHost .= "[br]";
						    	else
						        	$vipHost .= ", ";
							}
							$notice = str_replace("list_host_member", $memberHost, $noticeList['listHostSupport'][$userData['infoUser']['country']]);
							$notice = str_replace("list_host_vip", $vipHost, $notice);
							/* Post message */
							_postMessage($notice, $userData['infoUser']);
							/* Step loop */
							continue;
						}
					}
				}
			}
		}
	}
?>
	</body>
</html>		