<?php
	include_once('config.php');
	include_once('functions.php');
	if ((isset($_POST['user'])) && ($_POST['user'] != '') && (isset($_POST['secure'])) && ($_POST['secure'] == md5($configCpanel['password']))) {
		/* Process data */
		$userFile = "user/" . md5($_POST['user']) . ".dat";
		if (file_exists($userFile)) {
			/* Read data of user */
			$userData = json_decode(_readFile($userFile), true);
			/* Check */
			if (!$userData['leech']['status']) {
				$serverData = $server[$userData['infoUrl']['host']]['server'];
				unset($serverData[$userData['leech']['server']]);
				/* Get link */
				if (_getLink($userData['leech'], $serverData, $userData['infoUser']['rank'], $userData['infoUrl']['url'])) {
					/* Save data */
					$userData['leech']['status'] = true;
					if ($userData['infoUser']['rank'] == "member") {
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
					}
					else if ($userData['infoUser']['rank'] == "vip") {
						$userData['bandwith'][$userData['bandwith']['status']]['used'] = $userData['bandwith'][$userData['bandwith']['status']]['used'] + $userData['leech']['realSize'];
						$userData['bandwith'][$userData['bandwith']['status']]['left'] = $config['bandwithLimited']['vip'][$userData['bandwith']['status']] - $userData['bandwith'][$userData['bandwith']['status']]['used'];						
					}
					/* Get expired of vip (if user is vip) */
					$vipLeft = false;
					if ($userData['infoUser']['rank'] == 'vip') {
						$page = _curl('http://vnz-leech.com/mobilepay/check_vip.php?apikey=vnzleech_vip_checker&user=' . $userData['infoUser']['user'], '', '', 0);
						if ($page != '')
							$vipLeft = intval(abs($page - time()) / 86400) . ' day(s)';
					}						
					/* Generate bbcode */
					$styleList = array(
						'title' => $baseStyleList['title'][array_rand($baseStyleList['title'], 1)],
						'fileName' => $baseStyleList['fileName'][array_rand($baseStyleList['fileName'], 1)],
						'fileSize' => $baseStyleList['fileSize'][array_rand($baseStyleList['fileSize'], 1)] 
					);								
					$bbCode = "[center][b][img]http://i1157.photobucket.com/albums/p581/10steamxxx/smile10s/dl3.gif[/img][br][url=" . $userData['leech']['urlDownload'] . "][" . $styleList['title'] . "]VNZ.TEAM[/mau] | [img]https://www.google.com/s2/favicons?domain=" . $userData['infoUrl']['host'] . "[/img] [color=" . $styleList['fileName'] . "]" . $userData['leech']['fileName'] . "[/color] [color=" . $styleList['fileSize'] . "](" . $userData['leech']['fileSize'] . ")[/color] [img]http://i.imgur.com/RwpHhAy.gif[/img][/url]";
					if ($userData['bandwith']['status'] != false) 
						$bbCode .= "[br]([color=green]" . $noticeList['usedBandwith'][$userData['infoUser']['country']] . ":[/color] [color=red]" . _reconvertSize($userData['bandwith'][$userData['bandwith']['status']]['used']) . "[/color] | [color=green]" . $noticeList['leftBandwith'][$userData['infoUser']['country']] . ": [/color] [color=red]" . _reconvertSize($userData['bandwith'][$userData['bandwith']['status']]['left']) . "[/color] " . (($userData['infoUser']['rank'] == 'vip' && $vipLeft != false) ? " | [color=green]" . $noticeList['leftVip'][$userData['infoUser']['country']] . ":[/color] [color=red]" . $vipLeft . "[/color]" : "") . ")[/b][/center]";
					else
						$bbCode .= (($userData['infoUser']['rank'] == 'vip' && $vipLeft != false) ? "[br]([color=green]" . $noticeList['leftVip'][$userData['infoUser']['country']] . ":[/color] [color=red]" . $vipLeft . "[/color])" : "") . "[/b][/center]";
					/* Post message */
					_postMessage($bbCode, $userData['infoUser']);
				}			
				else {
					/* Write log */
					$errorLog = PHP_EOL . date('[Y-m-d H:i:s]') . ' ' . '[' . str_replace('www.', '', parse_url(explode('|', $server[$userData['infoUrl']['host']]['server'][$userData['leech']['server']])[0], PHP_URL_HOST)) . ']' . ' ' . $userData['leech']['error'];
					_writeFile('log/serverLog.dat', $errorLog, 'a');
				}
				/* Write data */
				_writeFile($userFile, json_encode($userData), 'w');
			}
		}
	}
	else
		echo "404 - Not found.";
?>