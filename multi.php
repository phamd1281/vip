<?php
	include_once('config.php');
	include_once('functions.php');
	if (isset($_POST['data']) && ($_POST['data'] != '') && isset($_POST['user']) && ($_POST['user'] != '') && isset($_POST['secure']) && ($_POST['secure'] == md5($configCpanel['password']))) {
		/* Process data */
		$userFile = "user/" . md5($_POST['user']) . ".dat";
		if (file_exists($userFile)) {
			/* Read data of user */
			$userData = json_decode(_readFile($userFile), true);
			/* DeJSON */
			$urls = json_decode($_POST['data'], true);
			if (empty($urls))
				die('404 - Not found.'); 
			/* Create bbCode */
			$result = array('success' => array(), 'fail' => array()); $hostList = array();
			foreach ($urls as $url) {
				/* Convert link */
				$userData['infoUrl']['url'] = str_replace('//ul.to', '//uploaded.net/file', $url);
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
				/* Regex */
				preg_match('/https?:\/\/(www.)?(.*)\/(.*)\/(.*)[\/]?/i', $url, $matches);
				$url = $matches[2] . '/' . $matches[3];
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
						/* Save result */
						array_push($result['fail'] , "[color=#0C6FE7]{$url}[/color] [img]http://i.imgur.com/x2QH9rg.gif[/img] {$noticeList['multiLink']['getInfoLink'][$userData['infoUrl']['status']][$userData['infoUser']['country']]}");
						/* Step loop */
						continue;
					}
					/* Check size limit */
					if (isset($config['sizeLimited']['vip'][$hostOriginal]) && ($config['sizeLimited']['vip'][$hostOriginal] > 0) && ($userData['infoUser']['rank'] == 'vip')) {
						if (($userData['infoUrl']['realSize'] > $config['sizeLimited']['vip'][$hostOriginal])) {
							$notice = str_replace("file_size", _reconvertSize($config['sizeLimited']['vip'][$hostOriginal]), $noticeList['multiLink']['sizeLimited'][$userData['infoUser']['country']]);
							$notice = str_replace("icon_host", "https://www.google.com/s2/favicons?domain=" . $hostOriginal, $notice);
							$notice = str_replace("name_host", ucfirst($hostOriginal), $notice);
							/* Save result */
							array_push($result['fail'] , "([color=#CD0000]{$userData['infoUrl']['fileName']}[/color] | {$userData['infoUrl']['fileSize']}) [img]http://i.imgur.com/x2QH9rg.gif[/img] {$notice}");
							/* Step loop */
							continue;
						}
					}
					/* Check bandwith */
					if (isset($config['bandwithLimited']['vip'][$hostOriginal]) && ($config['bandwithLimited']['vip'][$hostOriginal] > 0) && ($userData['infoUser']['rank'] == 'vip')) {
						if (!isset($userData['bandwith'][$hostOriginal]))
							$userData['bandwith'][$hostOriginal] = array();
						if (_checkBandwith($userData['bandwith'][$hostOriginal], $userData['infoUrl']['realSize'], $config['bandwithLimited']['vip'][$hostOriginal])) {
							if ($userData['bandwith'][$hostOriginal]['left'] - $userData['bandwith'][$hostOriginal]['used'] <= (1 * 1024 * 1024) || $userData['bandwith'][$hostOriginal]['used'] == 0) {
								$notice = str_replace("file_size", _reconvertSize($config['bandwithLimited']['vip'][$hostOriginal]), $noticeList['bandwithLimited']['vip']['reach'][$userData['infoUser']['country']]);
								$notice = str_replace("icon_host", "https://www.google.com/s2/favicons?domain=" . $hostOriginal, $notice);
								$notice = str_replace("name_host", ucfirst($hostOriginal), $notice);
							}
							else {
								$notice = str_replace("file_size", _reconvertSize($userData['bandwith'][$hostOriginal]['left'] - $userData['bandwith'][$hostOriginal]['used']), $noticeList['bandwithLimited']['vip']['reach'][$userData['infoUser']['country']]);
								$notice = str_replace("icon_host", "https://www.google.com/s2/favicons?domain=" . $hostOriginal, $notice);
								$notice = str_replace("name_host", ucfirst($hostOriginal), $notice);
							}
							/* Save result */
							array_push($result['fail'] , "([color=#CD0000]{$userData['infoUrl']['fileName']}[/color] | {$userData['infoUrl']['fileSize']}) [img]http://i.imgur.com/x2QH9rg.gif[/img] {$notice}");
							/* Step loop */
							continue;
						}
						/* Save data */								
						$userData['bandwith']['status'] = $hostOriginal;						
					}
					/* Get link */
					if (_getLink($userData['leech'], $server[$hostOriginal]['server'], $userData['infoUser']['rank'], $userData['infoUrl']['url'])) {
						if ($userData['bandwith']['status'] != false) {
							/* Save data */
							$userData['bandwith'][$userData['bandwith']['status']]['used'] = $userData['bandwith'][$userData['bandwith']['status']]['used'] + $userData['leech']['realSize'];
							$userData['bandwith'][$userData['bandwith']['status']]['left'] = $config['bandwithLimited']['vip'][$userData['bandwith']['status']] - $userData['bandwith'][$userData['bandwith']['status']]['used'];					
						}
						$styleList = array(
							'title' => $baseStyleList['title'][array_rand($baseStyleList['title'], 1)],
							'fileName' => $baseStyleList['fileName'][array_rand($baseStyleList['fileName'], 1)],
							'fileSize' => $baseStyleList['fileSize'][array_rand($baseStyleList['fileSize'], 1)] 
						);
						/* Save data */
						if (!in_array($hostOriginal, $hostList))
							array_push($hostList, $hostOriginal);
						/* Save result */
						array_push($result['success'], "[url=" . $userData['leech']['urlDownload'] . "][" . $styleList['title'] . "]VNZ.TEAM[/mau] | [img]https://www.google.com/s2/favicons?domain=" . $hostOriginal . "[/img] [color=" . $styleList['fileName'] . "]" . $userData['leech']['fileName'] . "[/color] [color=" . $styleList['fileSize'] . "](" . $userData['leech']['fileSize'] . ")[/color][/url]");
						/* Step loop */
						continue;
					}
					else {
						array_push($result['fail'] , "([color=#CD0000]{$userData['infoUrl']['fileName']}[/color] | {$userData['infoUrl']['fileSize']}) [img]http://i.imgur.com/x2QH9rg.gif[/img] {$noticeList['multiLink']['errorLeech'][$userData['infoUser']['country']]}");
						/* Write log */
						$errorLog = PHP_EOL . date('[Y-m-d H:i:s]') . ' ' . '[' . str_replace('www.', '', parse_url(explode('|', $server[$hostOriginal]['server'][$userData['leech']['server']])[0], PHP_URL_HOST)) . ']' . ' ' . $userData['leech']['error'];
						_writeFile('log/serverLog.dat', $errorLog, 'a');
						/* Step loop */
						continue;
					}
				}
				else if (in_array($hostOriginal, $hostOffline)) {
					$notice = str_replace("name_host", ucfirst($hostOriginal), $noticeList['multiLink']['hostOffline'][$userData['infoUser']['country']]);
					$notice = str_replace("icon_host", "https://www.google.com/s2/favicons?domain=" . $hostOriginal, $notice);
					/* Save data */
					array_push($result['fail'] , $notice);
					/* Step loop */
					continue;
				}
				else {
					$notice = str_replace("name_host", ucfirst($hostOriginal), $noticeList['multiLink']['notSupport'][$userData['infoUser']['country']]);
					$notice = str_replace("icon_host", "https://www.google.com/s2/favicons?domain=" . $hostOriginal, $notice);
					/* Save data */
					array_push($result['fail'] , $notice);
					/* Step loop */
					continue;
				}
			}
			if ($result['success']) {
				$bbCode = ''; $result['success'] = array_unique($result['success']);
				foreach($result['success'] as $temp)
					$bbCode .= $temp . "[br]";
				if ($userData['infoUser']['rank'] == "vip") {
					$vipLeft = false; $bandwith = '';
					$page = _curl('http://vnz-leech.com/mobilepay/check_vip.php?apikey=vnzleech_vip_checker&user=' . $userData['infoUser']['user'], '', '', 0);
					if ($page != '')
						$vipLeft = intval(abs($page - time()) / 86400) . ' day(s)';
					if (!empty($hostList))
						foreach($hostList as $host) {
							if (isset($userData['bandwith'][$host]))
								$bandwith .= '[img]https://www.google.com/s2/favicons?domain=' . $host . '[/img] [color=red]' . round($userData['bandwith'][$host]['used'] / (1024 * 1024 * 1024), 2) . '[/color]/[color=green]' . round($userData['bandwith'][$host]['left'] / (1024 * 1024 * 1024), 2) . '[/color] GB | ';
						}
				}
				$bbCode = "[center][b][img]http://i1157.photobucket.com/albums/p581/10steamxxx/smile10s/dl3.gif[/img][br]" . $bbCode . (($userData['infoUser']['rank'] == 'vip' && $vipLeft != false) ? "(" . (($bandwith != '') ? $bandwith : "") . "[color=green]" . $noticeList['leftVip'][$userData['infoUser']['country']] . ":[/color] [color=red]" . $vipLeft . "[/color])" : (($bandwith != '') ? "(" . $bandwith . ")" : "")) . "[/b][/center]";
				/* Post message */
				_postMessage($bbCode, $userData['infoUser']);
			}
			if ($result['fail']) {
				$bbCode = ''; $result['fail'] = array_unique($result['fail']);
				foreach($result['fail'] as $temp)
					$bbCode .= $temp . "[br]";
				$bbCode = "[center][b]{$bbCode}[/b][/center]";
				/* Post message */
				_postMessage($bbCode, $userData['infoUser']);
			}
			/* Write data */
			_writeFile($userFile, json_encode($userData), 'w');
		}
	}
	else
		echo "404 - Not found.";
?>