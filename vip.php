<meta http-equiv='refresh' content='2'>
<html>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<head>
		<title>Nothing at here ...</title>
	</head>
	<body>
<!-- xml version="1.0" encoding="utf-8" -->
<?php
	include_once('config.php');
	include_once('functions.php');
	echo 'Cbox: ' . $config['cboxUrl'] . '&sec=main' . '<br>';
	$page = file_get_contents($config['cboxUrl'] . "&sec=main") or die('Can\'t connect to cbox.');
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
		$infoUser['user'] = strtolower($infoUser['name']);

		preg_match('/<b class="(.*?)">(.*?)<\/b>/s', $line[$i], $matches);
		$infoUser['rank'] = $matches[1]; 
		/* PRIMARY */ 
		if (($config['work'] == true) && ($infoUser['user'] != strtolower($config['bot']['name']))) {
			/* Get message */
			preg_match('/<\/b>:(.*)/', $line[$i], $matches);
			$message = $matches[1];
			if (preg_match_all('/<a class="autoLink" href="(.*?)"/i', $message, $match, PREG_PATTERN_ORDER) && !preg_match('/\[media](.*)\[\/media]/', $line[$i])) {  
				/* Check user */
				if ((stristr($infoUser['rank'], 'nme pn_adm') || stristr($infoUser['rank'], 'nme pn_mod') || in_array($infoUser['name'], $adminList) || in_array($infoUser['user'], $vipList)) && !in_array($infoUser['name'], $blackList)) {	
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
								'rank' => ((stristr($infoUser['rank'], 'nme pn_adm') || in_array($infoUser['name'], $adminList)) ? "admin" : (stristr($infoUser['rank'], 'nme pn_mod') ? "mod" : "vip")), 
								'country' => $infoUser['country']
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
							$userData['infoUser']['rank'] = ((stristr($infoUser['rank'], 'nme pn_admin') || in_array($infoUser['name'], $adminList)) ? "admin" : (stristr($infoUser['rank'], 'nme pn_mod') ? "mod" : (in_array($infoUser['user'], $vipList) ? "vip" : "member")));
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
							preg_match('/<b class="(.*?)">(.*?)<\/b>/s', $line[$i], $matches);
							$userData['infoUser']['rank'] = ((stristr($matches[1], 'nme pn_admin') || in_array($userData['infoUser']['name'], $adminList)) ? "admin" : (stristr($matches[1], 'nme pn_mod') ? "mod" : (in_array($userData['infoUser']['name'], $vipList) ? "vip" : "member")));
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
						/* Check url */
						for ($i = 0; $i < count($match[1]); $i++)
							if (!_isValidUrl($match[1][$i]) && !stristr($match[1][$i], "uploadgig.com") && !stristr($match[1][$i], "nitroflare.com") && !stristr($match[1][$i], "hitfile.net") && !stristr($match[1][$i], "ex-load.com")) {
								unset($match[1][$i]);
								/* Post message */
								_postMessage($noticeList['requestError'][$userData['infoUser']['country']], $userData['infoUser']);
							}
							else if ($redirect = _isRedirectUrl($match[1][$i]))
								$match[1][$i] = $redirect;
						/* Check multi link */
						if (count($match[1]) > 1) {
							/* Check multi link again  */
							if (count($match[1]) > 4) {
								/* Post message */
								_postMessage($noticeList['multiLink']['limited'][$userData['infoUser']['country']], $userData['infoUser']);
								/* Step loop */
								continue;
							}
							else {
								/* Alert */
								$alert = str_replace("number_link", count($match[1]), $noticeList['multiLink']['alert'][$userData['infoUser']['country']]);
								_postMessage($alert, $userData['infoUser']);
								/* Write data */
								_writeFile($userFile, json_encode($userData), 'w');
								/* Request */
								$data = json_encode($match[1]);
								$data = str_replace('"', '\"', $data);
								if (substr(php_uname(), 0, 7) == "Windows")
									execInBackground("/D C:\wget\ wget " . $currentUrl . "/multi.php --post-data=\"data=" . $data . "&user=" . $userData['infoUser']['user'] . "&secure=" . md5($configCpanel['password']) . "\" --output-document=" . getcwd() . "\\temp.txt");
								else 
									execInBackground("wget " . $currentUrl . "/multi.php --post-data=\"data=" . $data . "&user=" . $userData['infoUser']['user'] . "&secure=" . md5($configCpanel['password']) . "\" --output-document=" . getcwd() . "\\temp.txt");								
								/* Step loop */
								continue;
							}
						}
						else if (count($match[1]) == 1) {
							/* Convert link */
							$urlOriginal = $match[1][0];
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
									/* Step loop */
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
								/* Get link from order's admin, mod */
								if (preg_match('/@ (.*?):/', $message, $matches) && ($userData['infoUser']['rank'] != 'vip')) {
									/* Get info of user */
									$otherUser = array();
									if (!_getInfo($otherUser ,strtolower($matches[1]))) {
										/* Step loop */
										continue;												
									}
									/* Get link */
									if (_getLink($userData['leech'], $server[$hostOriginal]['server'], $otherUser['rank'], $userData['infoUrl']['url'])) {
										/* Generate bbcode */
										$styleList = array(
											'title' => $baseStyleList['title'][array_rand($baseStyleList['title'], 1)],
											'fileName' => $baseStyleList['fileName'][array_rand($baseStyleList['fileName'], 1)],
											'fileSize' => $baseStyleList['fileSize'][array_rand($baseStyleList['fileSize'], 1)] 
										);								
										$bbCode = "[img]http://i.imgur.com/iNejzbq.gif[/img][center][b][img]http://i1157.photobucket.com/albums/p581/10steamxxx/smile10s/dl3.gif[/img][br][url=" . $userData['leech']['urlDownload'] . "][" . $styleList['title'] . "]VNZ.TEAM[/mau] | [img]https://www.google.com/s2/favicons?domain=" . $hostOriginal . "[/img] [color=" . $styleList['fileName'] . "]" . $userData['leech']['fileName'] . "[/color] [color=" . $styleList['fileSize'] . "](" . $userData['leech']['fileSize'] . ")[/color][/url][/b][/center]";
										/* Post message */
										_postMessage($bbCode, $otherUser);
										/* Step loop */
										continue;									
									}
									else {
										/* Write log */
										$errorLog = PHP_EOL . date('[Y-m-d H:i:s]') . ' ' . '[' . str_ireplace('www.', '', parse_url($server[$hostOriginal]['server'][$userData['leech']['server']], PHP_URL_HOST)) . ']' . ' ' . $userData['leech']['error'];
										_writeFile('log/serverLog.dat', $errorLog, 'a');
										/* Step loop */
										continue;
									}
								}								
								/* Check size limit */
								if (isset($config['sizeLimited']['vip'][$hostOriginal]) && ($config['sizeLimited']['vip'][$hostOriginal] > 0) && ($userData['infoUser']['rank'] == 'vip')) {
									if ($userData['infoUrl']['realSize'] > $config['sizeLimited']['vip'][$hostOriginal]) {
										$notice = str_replace("icon_host", "https://www.google.com/s2/favicons?domain=" . $hostOriginal, $noticeList['sizeLimited']['vip'][$userData['infoUser']['country']]);
										$notice = str_replace("name_host", ucfirst($hostOriginal), $notice);
										$notice = str_replace("file_size", _reconvertSize($config['sizeLimited']['vip'][$hostOriginal]), $notice);
										/* Post message */
										_postMessage($notice, $userData['infoUser']); 
										/* Step loop */
										continue;
									}
								}
								/* Check bandwith */
								if (isset($config['bandwithLimited']['vip'][$hostOriginal]) && ($config['bandwithLimited']['vip'][$hostOriginal] > 0) && ($userData['infoUser']['rank'] == 'vip')) {
									if (!isset($userData['bandwith'][$hostOriginal]))
										$userData['bandwith'][$hostOriginal] = array();
									if (_checkBandwith($userData['bandwith'][$hostOriginal], $userData['infoUrl']['realSize'], $config['bandwithLimited']['vip'][$hostOriginal])) {
										$remain = (strtotime(str_replace('/', '-', $userData['expired'])) + 86400) - time();
										if ($userData['bandwith'][$hostOriginal]['left'] - $userData['bandwith'][$hostOriginal]['used'] <= (1 * 1024 * 1024) && $userData['bandwith'][$hostOriginal]['used'] != 0) {
											$notice = str_replace("file_size", _reconvertSize($config['bandwithLimited']['vip'][$hostOriginal]), $noticeList['bandwithLimited']['vip']['reach'][$userData['infoUser']['country']]);
											$notice = str_replace("icon_host", "https://www.google.com/s2/favicons?domain=" . $hostOriginal, $notice);
											$notice = str_replace("name_host", ucfirst($hostOriginal), $notice);
											$notice = str_replace("time", "[cam]" . floor(($remain % 86400) / 3600) . " hour(s)[/mau] [tim]" . floor((($remain % 86400) % 3600) / 60) . " minute(s)[/mau] [den]" . floor((($remain % 86400) % 3600) % 60) ." second(s)[/mau]", $notice);
										}
										else {
											$notice = str_replace("file_size", _reconvertSize($userData['bandwith'][$hostOriginal]['left'] - $userData['bandwith'][$hostOriginal]['used']), $noticeList['bandwithLimited']['vip']['near'][$userData['infoUser']['country']]);
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
								/* Get link */
								if (_getLink($userData['leech'], $server[$hostOriginal]['server'], $userData['infoUser']['rank'], $userData['infoUrl']['url'])) {
									/* Save data */
									if ($userData['bandwith']['status'] != false) {
										$userData['bandwith'][$userData['bandwith']['status']]['used'] = $userData['bandwith'][$userData['bandwith']['status']]['used'] + $userData['leech']['realSize'];
										$userData['bandwith'][$userData['bandwith']['status']]['left'] = $config['bandwithLimited']['vip'][$userData['bandwith']['status']] - $userData['bandwith'][$userData['bandwith']['status']]['used'];
									}
									/* Get expired of vip (if user is vip) */
									$vipLeft = false;
									if ($userData['infoUser']['rank'] == 'vip') {
										$page = _curl('http://vnz-leech.com/mobilepay/check_vip.php?apikey=vnzleech_vip_checker&user=' . $userData['infoUser']['user'], '', '', 0);
										if ($page != '' && is_numeric($page))
											$vipLeft = intval(abs($page - time()) / 86400) . ' day(s)';
									}
									/* Generate bbcode */
									$styleList = array(
										'title' => $baseStyleList['title'][array_rand($baseStyleList['title'], 1)],
										'fileName' => $baseStyleList['fileName'][array_rand($baseStyleList['fileName'], 1)],
										'fileSize' => $baseStyleList['fileSize'][array_rand($baseStyleList['fileSize'], 1)] 
									);								
									$bbCode = "[center][b][img]http://i1157.photobucket.com/albums/p581/10steamxxx/smile10s/dl3.gif[/img][br][url=" . $userData['leech']['urlDownload'] . "][" . $styleList['title'] . "]VNZ.TEAM[/mau] | [img]https://www.google.com/s2/favicons?domain=" . $hostOriginal . "[/img] [color=" . $styleList['fileName'] . "]" . $userData['leech']['fileName'] . "[/color] [color=" . $styleList['fileSize'] . "](" . $userData['leech']['fileSize'] . ")[/color][/url]";
									if ($userData['bandwith']['status'] != false) 
										$bbCode .= "[br]([color=green]" . $noticeList['usedBandwith'][$userData['infoUser']['country']] . ":[/color] [color=red]" . _reconvertSize($userData['bandwith'][$userData['bandwith']['status']]['used']) . "[/color] | [color=green]" . $noticeList['leftBandwith'][$userData['infoUser']['country']] . ": [/color] [color=red]" . _reconvertSize($userData['bandwith'][$userData['bandwith']['status']]['left']) . "[/color]" . (($userData['infoUser']['rank'] == 'vip' && $vipLeft != false) ? " | [color=green]" . $noticeList['leftVip'][$userData['infoUser']['country']] . ":[/color] [color=red]" . $vipLeft . "[/color]" : "") . ")[/b][/center]";
									else
										$bbCode .= (($userData['infoUser']['rank'] == 'vip' && $vipLeft != false) ? "[br]([color=green]" . $noticeList['leftVip'][$userData['infoUser']['country']] . ":[/color] [color=red]" . $vipLeft . "[/color])" : "") . "[/b][/center]";
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
										$errorLog = PHP_EOL . date('[Y-m-d H:i:s]') . ' ' . '[' . $server[$hostOriginal]['server'][$userData['leech']['server']] . ']' . ' ' . $userData['leech']['error'];
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
								$vipHost = ''; $i = 0;
								foreach ($hostOnline as $host) {
									$vipHost .= "[color=#CD0000]" . ucfirst($host) . "[/color]";
									if ($i % 6 == 0 && $i != 0)
							        	$vipHost .= "[br]";
							    	else
							        	$vipHost .= ", ";
							        $i++;
								}
								$notice = str_replace("list_host_vip", $vipHost, $noticeList['listHostSupport'][$userData['infoUser']['country']]);
								$notice = str_replace("[tim]Member[/mau] [img]http://i.imgur.com/x2QH9rg.gif[/img] list_host_member [br]", '', $notice);
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
	}
?>
	</body>
</html>		