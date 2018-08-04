<?php
	date_default_timezone_set('Asia/Ho_Chi_Minh');
	set_time_limit(60);
	include_once('functions.php');
	################### Current Url ##########################
	$currentUrl = 'http://' . $_SERVER['SERVER_NAME'] . ($_SERVER['SERVER_PORT'] != 80 ? ':' . $_SERVER['SERVER_PORT'] : '') . str_replace('\\', '/', dirname($_SERVER['REQUEST_URI']));
	################### Config File ##########################
	$fileList = array(
		'config' => 'config/config.dat',
		'server' => 'config/server.dat',
		'admin' => 'config/admin.dat',
		'black' => 'config/black.dat',
		'badword' => 'config/badword.dat',
		'time' => 'temp/time.dat',
		'post' => 'temp/post.dat',
		'cookie' => 'temp/cookie.dat',
		'message' => 'temp/message.dat'
	);
	################### Decode JSON ##########################
	$config = json_decode(_readFile($fileList['config']), true);
	$server = json_decode(_readFile($fileList['server']), true);
	$adminList = json_decode(_readFile($fileList['admin']), true);
	$blackList = json_decode(_readFile($fileList['black']), true);
	$badwordList = json_decode(_readFile($fileList['badword']), true);
	################### Get VIP List ##########################
	$vipList = array();
	$page = file_get_contents("http://vnz-leech.com/vip/group.php");
	preg_match_all('/"(.*)"/', $page, $matches);
	foreach ($matches[1] as $match) {
		if (stristr($match, ',')) {
			$temps = explode(',', $match);
			foreach ($temps as $temp) 
				array_push($vipList, str_replace('"', '', strtolower($temp)));
		}
		else
			array_push($vipList, str_replace('"', '', strtolower($match)));
	}		
	################### Host Online List #####################
	$hostOnline = array();
	foreach ($server as $key => $value) 
		if($value['work'] == true)
			array_push($hostOnline, $key);
	################### Host Offline List ####################
	$hostOffline = array();
	foreach ($server as $key => $value) 
		if($value['work'] == false)
			array_push($hostOffline, $key);	
	################### IS Wget Exist ########################			
	if ((substr(php_uname(), 0, 7) == "Windows") && (!file_exists("C:\wget\\")) && (!is_dir("C:\wget\\")))
        pclose(popen("start /B xcopy " . getcwd() . "\wget C:\wget\\", "r"));
	
	################### Cpanel ###############################
	$configCpanel = array(
		'password' => 'encode'  
	);
	################## Some Config Other Need ################
	$baseStyleList = array(
		'title' => array('den', 'vang', 'hong', 'tim', 'cam', 'xanh', 'luc'),
		'fileName' => array('DarkSlateGray', '#CD0000', '#0174DF', '#04B45F', '#FF4500', '#FF0040', '#999903'),
		'fileSize' => array('#000000', 'brown', '#999903', '#FF0040'),
		'icon' => array('https://i.imgur.com/i88shFC.gif', 'https://i.imgur.com/6NYOxv5.gif', 'https://i.imgur.com/tPxYuCX.gif', 'https://i.imgur.com/YM9rz7t.gif', 'https://i.imgur.com/LELPDLN.gif', 'https://i.imgur.com/1p2zDib.gif', 'https://i.imgur.com/QgBWk6X.gif', 'https://i.imgur.com/TWyMcuk.gif', 'https://i.imgur.com/NSlqou9.gif', 'https://i.imgur.com/JMCRrn9.gif', 'https://i.imgur.com/zYvvXCz.gif', 'https://i.imgur.com/tyKjW4k.gif', 'https://i.imgur.com/6VzS8AR.gif', 'https://i.imgur.com/FGqgJla.gif' , 'https://i.imgur.com/1Ml5ZS6.gif', 'https://i.imgur.com/CiU6hax.gif', 'https://i.imgur.com/OW7MSPw.gif', 'https://i.imgur.com/gfP7i8N.gif', 'https://i.imgur.com/INrnKHI.gif', 'https://i.imgur.com/BjYAvRr.gif', 'https://i.imgur.com/qcD3ILq.gif', 'https://i.imgur.com/4g4Fd6v.gif', 'https://i.imgur.com/Erjp2HV.gif', 'https://i.imgur.com/fs1bcdb.gif', 'https://i.imgur.com/xcmYrYd.gif'),
		'admin' => 'vang',
		'vip' => 'cam',
		'member' => 'den',
	);
	################## Notice ################################
	$noticeList = array(
		'checkLink' => array(
			'vn' => '[center][b][color=green]Hãy kiểm tra link [/color][img]http://i.imgur.com/x2QH9rg.gif[/img][url=' . $config['checkUrl'] . '][vang] Tại đây [/mau][/url][color=green]trước khi gửi link[/color][/b][/center]',
			'us' => '[center][b][color=green]Please check link [/color][img]http://i.imgur.com/x2QH9rg.gif[/img][url=' . $config['checkUrl'] . '][vang] Here [/mau][/url][color=green]before send link[/color][/b][/center]'
		),
		'getInfoLink' => array(
			'errorLink' => array(
				'vn' => '[center][b][color=green]Gặp[/color] [vang]lỗi[/mau] [color=green]khi đang kiểm tra[/color] [den]link của bạn[/mau] | [color=orange]Liên hệ admin hoặc mod để trực tiếp để sửa lỗi[/color][/b][/center]',
				'us' => '[center][b][color=green]Something[/color] [vang]wrong[/mau] [color=green]when checking[/color] [den]your link[/mau] | [color=orange]Please contact admin or mod to fix it[/color][/b][/center]'
			),
			'folderLink' => array(
				'vn' => '[center][b][color=green]Sẽ sớm hỗ trợ[/color] [den]link thư mục của bạn[/mau] | [color=orange]Hãy thử lại sau[/color][/b][/center]',
				'us' => '[center][b][color=green]Will support[/color] [den]your link folder[/mau] [color=green]soon[/color] | [color=orange]Please post other link[/color][/b][/center]'
			),
			'deadLink' => array(
				'vn' => '[center][b][den]Link của bạn[/mau] [color=green]đã[/color] [vang]chết[/mau] | [color=orange]Hãy kiểm tra lại link[/color][/b][/center]',
				'us' => '[center][b][den]Your link[/mau] [color=green]is[/color] [vang]dead[/mau] | [color=orange]Please try to check your link again[/color][/b][/center]'
			),
			'requrePassword' => array(
				'vn' => '[center][b][den]Link của bạn[/mau] [color=green]yêu cầu[/color] [vang]mật khẩu[/mau] | [color=orange]Hãy gửi link kèm mật khẩu[/color] ([color=green]link[/color]|[color=red]password[/color]) [/b][/center]',
				'us' => '[center][b][den]Your link[/mau] [color=green]requred[/color] [vang]password[/mau] | [color=orange]Please post link with password again[/color][/b][/center]'
			),
			'wrongPassword' => array(
				'vn' => '[center][b][den]Mật khẩu của link[/mau] [color=green]không[/color] [vang]chính xác[/mau] | [color=orange]Hãy chắc chắn mật khẩu của link đã chính xác trước khi gửi link[/color][/b][/center]',
				'us' => '[center][b][den]Password of your link[/mau] [color=green]is[/color] [vang]not correct[/mau] | [color=orange]Please make sure your password of link is correct before post link[/color][/b][/center]'
			)
		),
		'hostLimited' => array(
			'vn' => '[center][b][img]icon_host[/img][den] name_host[/mau][color=green] chỉ được dùng cho [/color][vang]VIP[/mau] | [color=orange] Nâng cấp tài khoản VIP ngay bây giờ [/color][img]http://i.imgur.com/x2QH9rg.gif[/img][url=http://vnz-leech.com/donate/][hong] Tại đây [/mau][/url][/b][/center]',
			'us' => '[center][b][img]icon_host[/img][den] name_host[/mau][color=green] just only for [/color][vang]VIP[/mau] | [color=orange] Upgrade VIP now [/color][img]http://i.imgur.com/x2QH9rg.gif[/img][url=http://vnz-leech.com/donate/][hong] Here [/mau][/url][/b][/center]'
		),
		'sizeLimited' => array(
			'member' => array(
				'vn' => '[center][b][img]icon_host[/img][den] name_host[/mau][color=green] chỉ được leech dưới [/color][vang]file_size[/mau] | [color=orange] Nâng cấp tài khoản VIP ngay bây giờ [/color][img]http://i.imgur.com/x2QH9rg.gif[/img][url=http://vnz-leech.com/donate/][hong] Tại đây [/mau][/url][/b][/center]',
				'us' => '[center][b][img]icon_host[/img][den] name_host[/mau][color=green] just only leech in under [/color][vang]file_size[/mau] | [color=orange] Upgrade VIP now [/color][img]http://i.imgur.com/x2QH9rg.gif[/img][url=http://vnz-leech.com/donate/][hong] Here [/mau][/url][/b][/center]'
			),
			'vip' => array(
				'vn' => '[center][b][img]icon_host[/img][den] name_host[/mau][color=green] chỉ được leech dưới [/color][vang]file_size[/mau] | [color=orange] Hãy kiếm [/color][img]http://i.imgur.com/x2QH9rg.gif[/img][hong] link khác [/mau][/b][/center]',
				'us' => '[center][b][img]icon_host[/img][den] name_host[/mau][color=green] chỉ được leech dưới [/color][vang]file_size[/mau] | [color=orange] Just find [/color][img]http://i.imgur.com/x2QH9rg.gif[/img][hong] other link [/mau][/b][/center]'
			)
		),
		'timeLimited' => array(
			'minute' => array(
				'vn' => '[center][b][color=green]Vui lòng đợi [/color][vang]time_minute phút[/mau][color=green] trước khi gửi link tiếp theo[/color] | [color=orange] Nâng cấp tài khoản VIP ngay bây giờ [/color][img]http://i.imgur.com/x2QH9rg.gif[/img][url=http://vnz-leech.com/donate/][hong] Tại đây [/mau][/url][/b][/center]',
				'us' => '[center][b][color=green]Please waiting for [/color][vang]time_minute minute(s)[/mau][color=green] before send next link[/color] | [color=orange] Upgrade VIP now [/color][img]http://i.imgur.com/x2QH9rg.gif[/img][url=http://vnz-leech.com/donate/][hong] Here [/mau][/url][/b][/center]'
			),
			'second' => array(
				'vn' => '[center][b][color=green]Vui lòng đợi [/color][vang]time_second giây[/mau][color=green] trước khi gửi link tiếp theo[/color] | [color=orange] Nâng cấp tài khoản VIP ngay bây giờ [/color][img]http://i.imgur.com/x2QH9rg.gif[/img][url=http://vnz-leech.com/donate/][hong] Tại đây [/mau][/url][/b][/center]',
				'us' => '[center][b][color=green]Please waiting for [/color][vang]time_second second(s)[/mau][color=green] before send next link[/color] | [color=orange] Upgrade VIP now [/color][img]http://i.imgur.com/x2QH9rg.gif[/img][url=http://vnz-leech.com/donate/][hong] Here [/mau][/url][/b][/center]'
			),
			'min_sec' => array(
				'vn' => '[center][b][color=green]Vui lòng đợi [/color][vang]time_minute phút[/mau][den]time_second giây[/mau][color=green] trước khi gửi link tiếp theo[/color] | [color=orange] Nâng cấp tài khoản VIP ngay bây giờ [/color][img]http://i.imgur.com/x2QH9rg.gif[/img][url=http://vnz-leech.com/donate/][hong] Tại đây [/mau][/url][/b][/center]',
				'us' => '[center][b][color=green]Please waiting for [/color][vang]time_minute minute(s)[/mau][den]time_second second(s)[/mau][color=green] before send next link[/color] | [color=orange] Upgrade VIP now [/color][img]http://i.imgur.com/x2QH9rg.gif[/img][url=http://vnz-leech.com/donate/][hong] Here [/mau][/url][/b][/center]'
			)
		),
		'bandwithLimited' => array(
			'member' => array(
				'near' => array(
					'vn' => '[center][b][tim]Hôm nay[/mau], [color=green]Bạn chỉ còn[/color] [vang]file_size băng thông[/mau] [color=green]của[/color] [img]icon_host[/img][den] name_host[/mau] | [color=orange]Băng thông sẽ khôi phục lại sau[/color] [img]http://i.imgur.com/x2QH9rg.gif[/img] time[br][color=green]Hãy trở thành VIP để có thêm băng thông[/color] [img]http://i.imgur.com/x2QH9rg.gif[/img][url=http://vnz-leech.com/donate/][hong] Here [/mau][/url][/b][/center]',
					'us' => '[center][b][tim]Today[/mau], [color=green]You have left only[/color] [vang]file_size bandwidth[/mau] [color=green]of[/color] [img]icon_host[/img] [den]name_host[/mau] | [color=orange]Your bandwidth will reset after[/color] [img]http://i.imgur.com/x2QH9rg.gif[/img] time[br][color=green]Become VIP to get more bandwidth[/color] [img]http://i.imgur.com/x2QH9rg.gif[/img][url=http://vnz-leech.com/donate/][hong] Here [/mau][/url][/b][/center]'
				),
				'reach' => array(
					'vn' => '[center][b][tim]Hôm nay[/mau], [color=green]Bạn đã sử dụng hết [/color] [vang]file_size băng thông[/mau] [color=green]của[/color] [img]icon_host[/img] [den]name_host[/mau] | [color=orange]Băng thông sẽ khôi phục lại sau[/color] [img]http://i.imgur.com/x2QH9rg.gif[/img] time[br][color=green]Hãy trở thành VIP để có thêm băng thông[/color] [img]http://i.imgur.com/x2QH9rg.gif[/img][url=http://vnz-leech.com/donate/][hong] Here [/mau][/url][/b][/center]',
					'us' => '[center][b][tim]Today[/mau], [color=green]You\'ve used up [/color] [vang]file_size bandwidth[/mau][color=green] of [/color][img]icon_host[/img] [den]name_host[/mau] | [color=orange]Your bandwidth will reset after[/color] [img]http://i.imgur.com/x2QH9rg.gif[/img] time[br][color=green]Become VIP to get more bandwidth[/color] [img]http://i.imgur.com/x2QH9rg.gif[/img][url=http://vnz-leech.com/donate/][hong] Here [/mau][/url][/b][/center]'
				)
			),
			'vip' => array(
				'near' => array(
					'vn' => '[center][b][tim]Hôm nay[/mau], [color=green]Bạn chỉ còn[/color] [vang]file_size băng thông[/mau] [color=green]của[/color] [img]icon_host[/img][den] name_host[/mau] | [color=orange]Băng thông sẽ khôi phục lại sau[/color] [img]http://i.imgur.com/x2QH9rg.gif[/img] time[/b][/center]',
					'us' => '[center][b][tim]Today[/mau], [color=green]You have left only[/color] [vang]file_size bandwidth[/mau] [color=green]of[/color] [img]icon_host[/img] [den]name_host[/mau] | [color=orange]Your bandwidth will reset after[/color] [img]http://i.imgur.com/x2QH9rg.gif[/img] time[/b][/center]'
				),
				'reach' => array(
					'vn' => '[center][b][tim]Hôm nay[/mau], [color=green]Bạn đã sử dụng hết [/color] [vang]file_size băng thông[/mau] [color=green]của[/color] [img]icon_host[/img][den] name_host[/mau] | [color=orange]Băng thông sẽ khôi phục lại sau[/color] [img]http://i.imgur.com/x2QH9rg.gif[/img] time[/b][/center]',
					'us' => '[center][b][tim]Today[/mau], [color=green]You\'ve used up [/color] [vang]file_size bandwidth[/mau][color=green] of [/color][img]icon_host[/img][den] name_host[/mau] | [color=orange]Your bandwidth will reset after[/color] [img]http://i.imgur.com/x2QH9rg.gif[/img] time[/b][/center]'
				)
			)
		),
		'listHostSupport' => array(
			'vn' => '[center][b][vang]Vnz-Leech[/mau] [color=green]chỉ[/color] [den]hỗ trợ[/mau] [color=green]những host[/color][br][tim]Member[/mau] [img]http://i.imgur.com/x2QH9rg.gif[/img] list_host_member [br][hong]VIP[/mau] [img]http://i.imgur.com/x2QH9rg.gif[/img] list_host_vip [/b][/center]',
			'us' => '[center][b][vang]Vnz-Leech[/mau] [color=green]only[/color] [den]support[/mau] [color=green]hosts[/color][br][tim]Member[/mau] [img]http://i.imgur.com/x2QH9rg.gif[/img] list_host_member [br][hong]VIP[/mau] [img]http://i.imgur.com/x2QH9rg.gif[/img] list_host_vip [/b][/center]'
		),
		'notSupport' => array(
			'vn' => '[center][b][img]icon_host[/img] [den]name_host[/mau][color=green] không [/color][vang]hỗ trợ[/mau] | [color=orange] Hãy dùng [/color][img]http://i.imgur.com/x2QH9rg.gif[/img][hong] host khác [/mau][/b][/center]',
			'us' => '[center][b][img]icon_host[/img] [den] ame_host[/mau][color=green] not [/color][vang]supported[/mau] | [color=orange] Post [/color][img]http://i.imgur.com/x2QH9rg.gif[/img][hong] other host [/mau][/b][/center]'
		),
		'hostOffline' => array(
			'vn' => '[center][b][img]icon_host[/img] [den]name_host[/mau][color=green] đã [/color][vang]tắt[/mau] | [color=orange] Hãy dùng [/color][img]http://i.imgur.com/x2QH9rg.gif[/img][hong] host khác [/mau][/b][/center]',
			'us' => '[center][b][img]icon_host[/img] [den]name_host[/mau][color=green] is [/color][vang]offline[/mau] | [color=orange] Post [/color][img]http://i.imgur.com/x2QH9rg.gif[/img][hong] other host [/mau][/b][/center]'
		),
		'errorLeech' => array(
			'vn' => '[center][b][den]Tiến trình[/mau] [color=green]của bạn[/color] ([img]icon_host[/img] [color=#CD0000]file_name[/color] | file_size)[color=green] bị [/color][vang]lỗi[/mau] | [color=orange]Gửi lại link sau[/color] [img]http://i.imgur.com/x2QH9rg.gif[/img][hong] 1 phút [/mau][br][tim]Lỗi:[/mau] [color=red]error_status[/color] | [den]Máy chủ:[/mau] [color=green]server_leech[/color][/b][/center]',
			'us' => '[center][b][color=green]Your[/color] [den]process[/mau] ([img]icon_host[/img] [color=#CD0000]file_name[/color] | file_size)[color=green] have [/color][vang]error[/mau] | [color=orange]Post link again after[/color] [img]http://i.imgur.com/x2QH9rg.gif[/img][hong] 1 minute [/mau][br][tim]Error:[/mau] [color=red]error_status[/color] | [den]Server:[/mau] [color=green]server_leech[/color][/b][/center]'
		),
		'waitLeech' => array(
			'vn' => '[center][b][den]Tiến trình[/mau][color=green] của bạn [/color]([img]icon_host[/img] [color=#CD0000]file_name[/color] | file_size)[color=green] bị [/color][vang]lỗi[/mau] | [color=orange] Vui lòng đợi [/color][img]http://i.imgur.com/x2QH9rg.gif[/img][hong] ít giây nữa [/mau][br][tim]Lỗi:[/mau] [color=red]error_status[/color] | [den]Máy chủ:[/mau] [color=green]server_leech[/color][/b][/center]',
			'us' => '[center][b][color=green]Your [/color][den]process[/mau] ([img]icon_host[/img] [color=#CD0000]file_name[/color] | file_size)[color=green] have [/color][vang]error[/mau] | [color=orange] Wait for [/color][img]http://i.imgur.com/x2QH9rg.gif[/img][hong] a few second [/mau][br][tim]Error:[/mau] [color=red]error_status[/color] | [den]Server:[/mau] [color=green]server_leech[/color][/b][/center]'
		),
		'multiLink' => array(
			'limited' => array(
				'vn' => '[center][b][color=green]Bạn chỉ được[/color] [den]leech[/mau] [color=green]tối đa[/color] [vang]4 links[/mau] [color=green]cùng[/color] [hong]một lúc[/mau][/b][/center]',
				'us' => '[center][b][color=green]You just accepted[/color] [den]leech[/mau] [color=green]only[/color] [vang]4 links[/mau] [color=green]per[/color] [hong]one time[/mau][/b][/center]'
			),		
			'alert' => array(
				'vn' => '[center][b][color=green]Đã nhận được [/color][img]http://i.imgur.com/x2QH9rg.gif[/img][den] number_link link(s)[/mau] [color=green]của[/color] [vang]bạn[/mau] | [color=orange]Hãy đợi [/color][tim]ít phút[/mau][color=orange] để xử lý[/color][/b][/center]',
				'us' => '[center][b][color=green]Received this [/color][img]http://i.imgur.com/x2QH9rg.gif[/img][den] number_link link(s)[/mau][color=green] of [/color][vang]your[/mau] | [color=orange]Wait [/color][tim]a few minutes[/mau][color=orange] to processing it[/color][/b][/center]'
			),
			'getInfoLink' => array (
				'errorLink' => array(
					'vn' => '[color=green]Gặp[/color] [vang]lỗi[/mau] [color=green]khi đang kiểm tra[/color] [den]link của bạn[/mau]',
					'us' => '[color=green]Something[/color] [vang]wrong[/mau] [color=green]when checking[/color] [den]your link[/mau]'
				),
				'folderLink' => array(
					'vn' => '[color=green]Sẽ sớm hỗ trợ[/color] [den]link thư mục của bạn[/mau]',
					'us' => '[color=green]Will support[/color] [den]your link folder[/mau] [color=green]soon[/color]'
				),
				'deadLink' => array(
					'vn' => '[den]Link của bạn[/mau] [color=green]đã[/color] [vang]chết[/mau]',
					'us' => '[den]Your link[/mau] [color=green]is[/color] [vang]dead[/mau]'
				),
				'requrePassword' => array(
					'vn' => '[den]Link của bạn[/mau] [color=green]yêu cầu[/color] [vang]mật khẩu[/mau]',
					'us' => '[den]Your link[/mau] [color=green]requred[/color] [vang]password[/mau]'
				),
				'wrongPassword' => array(
					'vn' => '[den]Mật khẩu của link[/mau] [color=green]không[/color] [vang]chính xác[/mau]',
					'us' => '[den]Password of your link[/mau] [color=green]is[/color] [vang]not correct[/mau]'
				)				
			),
			'sizeLimited' => array(
				'vn' => '[img]icon_host[/img] [den]name_host[/mau] [color=green]chỉ được leech dưới[/color] [vang]file_size[/mau]',
				'us' => '[img]icon_host[/img] [den]name_host[/mau] [color=green]only leech under[/color] [vang]file_size[/mau]'
			),
			'bandwithLimited' => array(
				'near' => array(
					'vn' => '[color=green]Bạn chỉ còn[/color] [vang]file_size băng thông[/mau] [color=green]của[/color] [img]icon_host[/img][den] name_host[/mau]',
					'us' => '[color=green]You have left only[/color] [vang]file_size bandwidth[/mau] [color=green]of[/color] [img]icon_host[/img] [den]name_host[/mau]'
				),
				'reach' => array(
					'vn' => '[color=green]Bạn đã sử dụng hết [/color] [vang]file_size băng thông[/mau] [color=green]của[/color] [img]icon_host[/img][den] name_host[/mau]',
					'us' => '[color=green]You\'ve used up [/color] [vang]file_size bandwidth[/mau][color=green] of [/color][img]icon_host[/img][den] name_host[/mau]'
				)
			),
			'notSupport' => array(
				'vn' => '[img]icon_host[/img] [den]name_host[/mau] [color=green]không[/color] [vang]hỗ trợ[/mau]',
				'us' => '[img]icon_host[/img] [den]name_host[/mau] [color=green]not[/color] [vang]supported[/mau]'
			),
			'hostOffline' => array(
				'vn' => '[img]icon_host[/img] [den]name_host[/mau] [color=green]đang[/color] [vang]tắt[/mau]',
				'us' => '[img]icon_host[/img] [den]name_host[/mau] [color=green]is[/color] [vang]offline[/mau]'
			),
			'errorLeech' => array(
				'vn' => '[den]Tiến trình[/mau] [color=green]của bạn bị[/color] [vang]lỗi[/mau]',
				'us' => '[den]Your process[/mau] [color=green]have[/color] [vang]error[/mau]'
			),
		),
		'requestError' => array(
			'vn' => '[center][b][den]Không thể[/mau] [color=green]kết nối đến[/color] [tim]link của bạn[/mau], [color=green]có thể link đã[/color] [vang]chết[/mau] | [color=orange]Hãy kiểm tra link của bạn để chắc chắn link còn[/color] [luc]sống[/mau][/b][/center]',
			'us' => '[center][b][den]Can\'t[/mau] [color=green]request to[/color] [tim]your link[/mau], [color=green]maybe it\'s[/color] [vang]dead[/mau] | [color=orange]Please check to make sure your link is[/color] [luc]live[/mau][/b][/center]'
		),
		'usedBandwith' => array(
			'vn' => 'Đã sử dụng',
			'us' => 'Used'
		),
		'leftBandwith' => array(
			'vn' => 'Còn lại',
			'us' => 'Left'
		),
		'leftVip' => array(
			'vn' => 'Hạn VIP còn lại',
			'us' => 'VIP left'
		),
	);
	################### Command ##############################
	$command = array(
		'start_bot' => 'bot on',
		'stop_bot' => 'bot off',
		'start_talk' => 'talk on',
		'stop_talk' => 'talk off',
		'start_bot_post_mess' => 'post on',
		'stop_bot_post_mess' => 'post off',
		'start_ziplink' => 'ziplink on',
		'stop_ziplink' => 'ziplink off',
		'start_bandwith' => 'bandwith on',
		'stop_bandwith' => 'bandwith off',
		'start_host' => 'host on',
		'stop_host' => 'host off',
		'check_admin' => 'check admin',
		'add_admin' => 'add admin',
		'remove_admin' => 'del admin',
		'check_manager' => 'check manager',
		'add_manager' => 'add manager',
		'remove_manager' => 'del manager',
		'info_vip' => 'info vip',
		'check_vip' => 'check vip',
		'add_vip' => 'add vip',
		'remove_vip' => 'del vip',
		'check_blacklist' => 'check bl',
		'add_blacklist' => 'add bl',
		'remove_blacklist' => 'del bl',
		'check_ip' => 'check ip',
		'delete_message' => 'delmess',
		'bandel_user' => 'bandel',
		'ban_user' => 'ban user',
		'unban_user' => 'ub user',
		'check_3x' => 'check 3x',
		'add_3x' => 'add 3x',
		'remove_3x' => 'del 3x',
		'music_play' => 'music',
		'video_play' => 'video',
		'guide_bot' => 'guide bot',
		'guide_control_bot' => 'guide control bot',
		'guide_manage_bot' => 'guide manage bot',
		'guide_chat_bot' => 'guide chat bot',
		'guide_relax_bot' => 'guide relax bot',
	);
	
	################### Guide BOT ###########################
	$guide_bot = array(
		'guide_control_bot' => $command['guide_control_bot'] . '| Hướng dẫn điều khiển BOT',
		'guide_manage_bot' => $command['guide_manage_bot'] . '| Hướng dẫn quản lý danh sách của BOT',
		'guide_chat_bot' => $command['guide_chat_bot'] . '| Hướng dẫn quản lý và điều khiển Cbox',
		'guide_relax_bot' => $command['guide_relax_bot'] . '| Hướng dẫn sử dụng chế độ giải trí của BOT',
	);
	################### Guide BOT ############################
	
	################### Guide Control BOT ####################
	$guide_control_bot = array(
		'start_bot' => $command['start_bot'] . '| Khởi động BOT',
		'stop_bot' => $command['stop_bot'] . '| Tắt BOT',
		'start_talk' => $command['start_talk'] . '| Khởi động chế độ chém gió đa cấp của BOT',
		'stop_talk' => $command['stop_talk'] . '| Tắt chế độ chém gió đa cấp của BOT',
		'start_bot_post_mess' => $command['start_bot_post_mess'] . '| Khởi chế độ spam thần thánh của BOT',
		'stop_bot_post_mess' => $command['stop_bot_post_mess'] . '| Tắt chế độ spam thần thánh của BOT',
		'start_ziplink' => $command['start_ziplink'] . '| Khởi động chế độ siêu rút ngắn link của BOT',
		'stop_ziplink' => $command['stop_ziplink'] . '| Tắt chế độ siêu rút ngắn link của BOT',
		'start_bandwith' => $command['start_bandwith'] . '| Khởi động chế độ tính băng thông của BOT',
		'stop_bandwith' => $command['stop_bandwith'] . '| Tắt chế độ tính băng thông của BOT',
		'start_host' => $command['start_host'] . '| Khởi động host của BOT',
		'stop_host' => $command['stop_host'] . '| Tắt động host của BOT',
	);
	################### Guide Control BOT ####################
	
	################### Guide Manage BOT #####################
	$guide_manage_bot = array(
		'check_admin' => $command['check_admin'] . '| Kiểm tra danh sách Admin của BOT',
		'add_admin' => $command['add_admin'] . '| Thêm thành viên vào danh sách Admin của BOT',
		'remove_admin' => $command['remove_admin'] . '| Xóa thành viên khỏi danh sách Admin của BOT',
		'check_manager' => $command['check_manager'] . '| Kiểm tra danh sách Manager của BOT',
		'add_manager' => $command['add_manager'] . '| Thêm thành viên vào danh sách Manager của BOT',
		'remove_manager' => $command['remove_manager'] . '| Xóa thành viên khỏi danh sách Manager của BOT',
		'check_vip' => $command['check_vip'] . '| Kiểm tra danh sách VIP của BOT',
		'add_vip' => $command['add_vip'] . '| Thêm thành viên vào danh sách VIP của BOT',
		'remove_vip' => $command['remove_vip'] . '| Xóa thành viên khỏi danh sách VIP của BOT',
		'check_blacklist' => $command['check_blacklist'] . '| Kiểm tra danh sách Black của BOT',
		'add_blacklist' => $command['add_blacklist'] . '| Thêm thành viên vào danh sách Black của BOT',
		'remove_blacklist' => $command['remove_blacklist'] . '| Xóa thành viên khỏi danh sách Black của BOT'
	);
	################### Guide Manage BOT #####################
	
	################### Guide Chat BOT #######################
	$guide_chat_bot = array(
		'delete_message' => $command['delete_message'] . '| Xóa toàn bộ tin nhắn của thành viên',
		'check_ip' => $command['check_ip'] . '| Kiểm tra địa chỉ truy cập của thành viên',
		'bandel_user' => $command['bandel_user'] . '| Cấm thành viên truy cập vào Cbox và xóa toàn bộ tin nhắn của thành viên',
		'ban_user' => $command['ban_user'] . '| Cấm thành viên truy cập vào Cbox',
		'unban_user' => $command['add_manager'] . '| Xóa bỏ lệnh cấm thành viên truy cập Cbox'
	);
	################### Guide Chat BOT #######################
	
	################### Guide Relax BOT ######################
	$guide_relax_bot = array(
		'music_play' => $command['music_play'] . '| Phát nhạc theo yêu cầu của thành viên',
		'video_play' => $command['video_play'] . '| Phát video theo yêu cầu của thành viên'
	);
	################### Guide Relax BOT ######################
	
?>