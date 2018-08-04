<?php
	include_once('config.php');
	if (isset($_GET['url']) && $_GET['url'] != '' && isset($_GET['secure']) && $_GET['secure'] == md5($configCpanel['password'])) {
		if (preg_match('#((https|http)://(\S*?\.\S*?))([\s)\[\]{},;"\':<]|\.\s|$)#i', urldecode($_GET['url']))) {
			/* Convert link */
			$url = urldecode($_GET['url']);
			$url = str_replace('//ul.to', '//uploaded.net/file', $url);
			$url = str_replace('//4shared.com', '//4shared.com', $url);
			$url = str_replace('//sendspace.com', '//sendspace.com', $url);
			$url = str_replace('//uppit.com', '//uppit.com', $url);
			$url = str_replace('//uploaded.to', '//uploaded.net', $url);
			$url = str_replace('//d01.megashares.com', '//megashares.com', $url);
			$url = str_replace('//share-online.biz', '//share-online.biz', $url);
			$url = str_replace('//megairon.net', '//megairon.net', $url);
			$url = str_replace('//depositfile.org', '//depositfile.com', $url);
			$url = str_replace('//uplea.com', '//uplea.com', $url);
			$url = str_replace('//uploadrocket.net', '//uploadrocket.net', $url);
			$url = str_replace('//filesflash.net', '//filesflash.com', $url);
			$url = str_replace('//megairon.net', '//megairon.net', $url);
			$url = str_replace('//fileflyer.com', '//fileflyer.com', $url);
			$url = str_replace('//u26006872.letitbit.net', 'letitbit.net', $url);
			$url = str_replace('//8s8q0nr6ew.1fichier.com', '1fichier.com', $url);
			$url = str_replace('//rg.to', '//rapidgator.net', $url);
			$url = str_replace('//up.4share.vn', '//4share.vn', $url);
			$url = str_replace('//www45.zippyshare.com', '//zippyshare.com', $url);
			$url = str_replace(array('//k2s.cc', '//keep2s.cc'), '//keep2share.cc', $url);
			$url = str_replace('yunfile.com', 'dfpan.com', $url);
			$url = str_replace('//m.turbobit.net', '//turbobit.net', $url);
			$url = str_replace('//dfiles.eu', '//depositfiles.com', $url);
			$url = str_replace('%7C', '|', $url);
			/* Check link */
			$result = array('status' => 0);
			if (stristr($url, "katfile.com")) {
				$page = file_get_contents($url) or die(json_encode(array('status' => 0, 'error' => 'dead_link')));
				if (stristr($page, "style=\"font-weight:bold;color:#2578c7;\">") && stristr($page, "style=\"color:#555;\">")) {
					$result['status'] = 1;
					$result['url'] = $url;
					$result['filename'] = _cutStr($page, "style=\"font-weight:bold;color:#2578c7;\">", "</span>");
					$result['filesize'] = _cutStr($page, "style=\"color:#555;\">", "</span>");
				}
				else
					$result['error'] = 'dead_link';
			}
			else if (stristr($url, "1fichier.com")) {
				$page = file_get_contents($url) or die(json_encode(array('status' => 0, 'error' => 'dead_link')));
				if (stristr($page, "style=\"width:180px\">Filename") && stristr($page, "class=\"titre normal\">Size")) {
					$result['status'] = 1;
					$result['url'] = $url;
					/* Don't delete space */
					$result['filename'] = _cutStr($page, "Filename :</td>
				<td class=\"normal\">", "</td>");
					$result['filesize'] = _cutStr($page, "Size :</td><td class=\"normal\">", "</td>");					
				}
				else
					$result['error'] = 'dead_link';	
			}
			else if (stristr($url, "isra.cloud")) {
				$page = file_get_contents($url) or die(json_encode(array('status' => 0, 'error' => 'dead_link')));
				if (stristr($page, "File Not Found")) 
					$result['error'] = 'dead_link';	
				else if (stristr($page, "This file is available for Premium Users only")) {
					$result['status'] = 1;
					$result['url'] = $url;
					$result['filename'] = _cutStr($page, '<font style="color:darkred">', '</font>');
					$result['filesize'] = _cutStr($page, '<font style="color:#114">', '</font>');	
				}
				else {
					$result['status'] = 1;
					$result['url'] = $url;
					$result['filename'] = _cutStr($page, '<span class="dfilename">', '</span>');
					$result['filesize'] = _cutStr($page, '<span><b>', '</b></span>');					
				}
			}
			else
				$result['error'] = 'not_support';
			echo json_encode($result);
		}
		else
			echo '404 - Not Found.';
	}
	else 
		echo '404 - Not Found.';
?>