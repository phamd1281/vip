function _alert(type, message) {
	$('.alert').addClass('alert-' + type).html(message).slideDown('slow');
	setTimeout(function() { 
		$('.alert').slideUp('slow');
	}, 1000);
}
function _convertSize(size) {
	if (size.indexOf('TB') != -1)
		return size.substr(0, size.indexOf('TB')) * 1024 * 1024 * 1024 * 1024;
	else if (size.indexOf('GB') != -1)
		return size.substr(0, size.indexOf('GB')) * 1024 * 1024 * 1024;
	else if (size.indexOf('MB') != -1)
		return size.substr(0, size.indexOf('MB')) * 1024 * 1024;
	else if (size.indexOf('KB') != -1)
		return size.substr(0, size.indexOf('KB')) * 1024;
	else if (size.indexOf('B') != -1)
		return size.substr(0, size.indexOf('B'));
	else
		return 0;
}
function _compareArray(value, other) {
	var type = Object.prototype.toString.call(value);

	if (type !== Object.prototype.toString.call(other)) 
		return false;
	if (['[object Array]', '[object Object]'].indexOf(type) < 0) 
		return false;

	var valueLen = type === '[object Array]' ? value.length : Object.keys(value).length;
	var otherLen = type === '[object Array]' ? other.length : Object.keys(other).length;
	if (valueLen !== otherLen) 
		return false;
	/* Function */
	var compare = function(item1, item2) {
		var itemType = Object.prototype.toString.call(item1);

		if (['[object Array]', '[object Object]'].indexOf(itemType) >= 0) 
			if (!isEqual(item1, item2)) 
				return false;
		else {
			if (itemType !== Object.prototype.toString.call(item2)) 
				return false;
			if (itemType === '[object Function]') 
				if (item1.toString() !== item2.toString()) 
					return false;
			else 
				if (item1 !== item2) 
					return false;
		}
	};
	/* Compare properties */
	if (type === '[object Array]') 
		for (var i = 0; i < valueLen; i++) 
			if (compare(value[i], other[i]) === false) 
				return false;
	else 
		for (var key in value) 
			if (value.hasOwnProperty(key)) 
				if (compare(value[key], other[key]) === false) 
					return false;
	return true;
};

jQuery(document).ready(function() {
	var backup = {
		'work': $('span[id=work]').text(),
		'chat': $('span[id=chat]').text(),
		'zip': $('span[id=zip]').text(),
		'check3x': $('span[id=check3x]').text(),
		'userBot': $('div[id=userBot]').html(),
		'cboxUrl': $('input[id=cboxUrl]').val(),
		'checkUrl': $('input[id=checkUrl]').val(),
		'zipUrl': $('input[id=zipUrl]').val(),
		'admin': $('div[id=adminList]').html(),
		'badword': $('div[id=badwordList').html(),
		'parameterCheck': $('div[id=parameterCheckList').html(),
		'hostLimited': $('div[id=hostLimitedList').html(),
		'sizeLimited': $('div[id=sizeLimitedList').html(),
		'bandwithLimited': $('div[id=bandwithLimited').html(),
		'sizeVipLimited': $('div[id=sizeVipLimitedList').html(),
		'bandwithVipLimited': $('div[id=bandwithVipLimitedList]').html()
	};
	$(document).on('click', 'div[class=card-body] span', function() {
		var id = $(this).attr('id');
		if (id == 'work' || id == 'chat' || id == 'zip' || id == 'check3x') {
			if ($('span[id=' + id + ']').text() == 'True')
				$('span[id=' + id + ']').attr('class', 'badge badge-danger').text('False');
			else
				$('span[id=' + id + ']').attr('class', 'badge badge-success').text('True');
			if (!$('div[id=bot]').is(':visible'))
				$('div[id=bot]').slideDown('slow');
		}
		else {
			if (!$('div[id=' + id + ']').is(':visible'))
				$('div[id=' + id + ']').slideDown('slow');
		}
	});
	$(document).on('click', 'div[class=card-footer] button', function () {
		var id = $(this).parent().parent().attr('id');
		if (id == 'bot') {
			control = $(this).attr('id');
			if (control == 'save') {
				var bot = {}; var check = {};
				var span = ['work', 'chat', 'zip', 'check3x'];
				$.each(span, function(index, item) {
					if ($('span[id=' + item + ']').text() == 'True')
						bot[item] = true;
					else if ($('span[id=' + item + ']').text() == 'False')
						bot[item] = false;
				});
				if (!_compareArray(bot, check)) {
					$.ajax({
						url: 'cpanel.php',
						method: 'POST',
						data: 'type=savedata&t=config&d=' + JSON.stringify(bot),
						beforeSend: function() {
							$('div[id=' + id + ']').slideUp('slow');
						},
						success: function() {
							_alert('success', '<b>Successed!</b> Update config.');
						},
						error: function(error) {
							_alert('danger', '<b>Failed!</b> ' + error + '.');
						}
					});
				}
			}
			else if (control == 'reset') {
				var span = ['work', 'chat', 'zip', 'check3x'];
				$.each(span, function(index, item) {
					if (backup[item] == 'True')
						$('span[id=' + item + ']').attr('class', 'badge badge-success').text('True');
					else
						$('span[id=' + item + ']').attr('class', 'badge badge-danger').text('False');
				});
			}
		}
		else if (id == 'userBot') {
			control = $(this).attr('id');
			if (control == 'get')
				if (!$('div[id=getKey]').is(':visible'))
					$('div[id=getKey]').slideDown('slow');
			else if (control == 'save') {
				var name = $('input[id=nameBot]').val();
				var key = $('input[id=keyBot]').val();
				if (name != 0 && key != 0) {
					var payload = {'bot': {'name': name, 'key': key}};
					$.ajax({
						url: 'cpanel.php',
						method: 'POST',
						data: 'type=savedata&t=config&d=' + JSON.stringify(payload),
						beforeSend: function() {
							$('div[id=' + id + ']').slideUp('slow');
						},
						success: function() {
							_alert('success', '<b>Successed!</b> Update config.');
						},
						error: function(error) {
							_alert('danger', '<b>Failed!</b> ' + error + '.');
						}
					});					
				}
				else
					_alert('danger', '<b>Failed!</b> Name or Key is empty.');
			}
			else if (control == 'reset')
				$('input[id=' + id + ']').val(backup[id]);			
		}
		else if (id == 'cboxUrl') {
			control = $(this).attr('id');
			if (control == 'save') {
				var url = $('input[id=' + id + ']').val();
				if (url != '') {
					var payload = {'cboxUrl': url};
					$.ajax({
						url: 'cpanel.php',
						method: 'POST',
						data: 'type=savedata&t=config&d=' + JSON.stringify(payload),
						beforeSend: function() {
							$('div[id=' + id + ']').slideUp('slow');
						},
						success: function() {
							_alert('success', '<b>Successed!</b> Update config.');
						},
						error: function(error) {
							_alert('danger', '<b>Failed!</b> ' + error + '.');
						}
					});					
				}
				else
					_alert('danger', '<b>Failed!</b> Url cbox is empty.');
			}
			else if (control == 'reset')
				$('input[id=' + id + ']').val(backup[id]);
		}
		else if (id == 'checkUrl') {
			control = $(this).attr('id');
			if (control == 'save') {
				var url = $('input[id=' + id + ']').val();
				if (url != '') {
					var payload = {'checkUrl': url};
					$.ajax({
						url: 'cpanel.php',
						method: 'POST',
						data: 'type=savedata&t=config&d=' + JSON.stringify(payload),
						beforeSend: function() {
							$('div[id=' + id + ']').slideUp('slow');
						},
						success: function() {
							_alert('success', '<b>Successed!</b> Update config.');
						},
						error: function(error) {
							_alert('danger', '<b>Failed!</b> ' + error + '.');
						}
					});					
				}
				else
					_alert('danger', '<b>Failed!</b> Url cbox is empty.');
			}
			else if (control == 'reset')
				$('input[id=' + id + ']').val(backup[id]);
		}
		else if (id == 'zipUrl') {
			control = $(this).attr('id');
			if (control == 'save') {
				var url = $('input[id=' + id + ']').val();
				if (url != '') {
					var payload = {'zipUrl': url};
					$.ajax({
						url: 'cpanel.php',
						method: 'POST',
						data: 'type=savedata&t=config&d=' + JSON.stringify(payload),
						beforeSend: function() {
							$('div[id=' + id + ']').slideUp('slow');
						},
						success: function() {
							_alert('success', '<b>Successed!</b> Update config.');
						},
						error: function(error) {
							_alert('danger', '<b>Failed!</b> ' + error + '.');
						}
					});					
				}
				else
					_alert('danger', '<b>Failed!</b> Url cbox is empty.');
			}
			else if (control == 'reset')
				$('input[id=' + id + ']').val(backup[id]);
		}
		else if (id == 'admin') {
			control = $(this).attr('id');
			if (control == 'add') {
				$('div[id=' + id + 'List]').append('<div class="row" style="margin-top: 10px;"><div class="col col-sm-4"><input type="text" class="form-control" value=""></div><div class="col col-md-4"><button id="remove" class="btn btn-sm btn-flat btn-danger" style="margin-top: 3px;"><i class="fa fa-ban"></i>&nbsp; Remove</button></div></div>');
			}
			else if (control == 'save') {
				var adminList = []; var check = [];
				$('div[id=' + id + 'List]').children('div[class=row]').children('div').children('input').each(function() {
					if ($(this).length > 0 && $(this).val() != '')
						adminList.push($(this).val());
				});
				if (!_compareArray(adminList, check)) {
					$.ajax({
						url: 'cpanel.php',
						method: 'POST',
						data: 'type=savedata&t=admin&d=' + JSON.stringify(adminList),
						beforeSend: function() {
							$('div[id=' + id + ']').slideUp('slow');
						},
						success: function() {
							_alert('success', '<b>Successed!</b> Update admin list.');
						},
						error: function(error) {
							_alert('danger', '<b>Failed!</b> ' + error + '.');
						}
					});					
				}
				else
					_alert('danger', '<b>Failed!</b> Admin list is empty.');
			}	
			else if (control == 'reset')
				$('div[id=' + id + 'List]').html(backup[id]);		
		}
		else if (id == 'badword') {
			control = $(this).attr('id');
			if (control == 'add') {
				$('div[id=' + id + 'List]').append('<div class="row" style="margin-top: 10px;"><div class="col col-sm-4"><input type="text" class="form-control" value=""></div><div class="col col-md-4"><button id="remove" class="btn btn-sm btn-flat btn-danger" style="margin-top: 3px;"><i class="fa fa-ban"></i>&nbsp; Remove</button></div></div>');
			}
			else if (control == 'save') {
				var badwordList = []; var check = [];
				$('div[id=' + id + 'List]').children('div[class=row]').children('div').children('input').each(function() {
					if ($(this).length > 0 && $(this).val() != '')
						badwordList.push($(this).val());
				});
				if (!_compareArray(badwordList, check)) {
					$.ajax({
						url: 'cpanel.php',
						method: 'POST',
						data: 'type=savedata&t=badword&d=' + JSON.stringify(badwordList),
						beforeSend: function() {
							$('div[id=' + id + ']').slideUp('slow');
						},
						success: function() {
							_alert('success', '<b>Successed!</b> Update bad word list.');
						},
						error: function(error) {
							_alert('danger', '<b>Failed!</b> ' + error + '.');
						}
					});					
				}
				else
					_alert('danger', '<b>Failed!</b> Bad word list is empty.');
			}	
			else if (control == 'reset')
				$('div[id=' + id + 'List]').html(backup[id]);			
		}
		else if (id == 'parameterCheck') {
			control = $(this).attr('id');
			if (control == 'add') {
				$('div[id=' + id + 'List]').append('<div class="row" style="margin-top: 10px;"><div class="col col-sm-4"><input type="text" class="form-control" value=""></div><div class="col col-md-4"><button id="remove" class="btn btn-sm btn-flat btn-danger" style="margin-top: 3px;"><i class="fa fa-ban"></i>&nbsp; Remove</button></div></div>');
			}
			else if (control == 'save') {
				var parameterCheckList = []; var check = [];
				$('div[id=' + id + 'List]').children('div[class=row]').children('div').children('input').each(function() {
					if ($(this).length > 0 && $(this).val() != '')
						parameterCheck.push($(this).val());
				});
				if (!_compareArray(parameterCheckList, check)) {
					var payload = {'parameterCheck': parameterCheckList}
					$.ajax({
						url: 'cpanel.php',
						method: 'POST',
						data: 'type=savedata&t=config&d=' + JSON.stringify(payload),
						beforeSend: function() {
							$('div[id=' + id + ']').slideUp('slow');
						},
						success: function() {
							_alert('success', '<b>Successed!</b> Update parameter check list.');
						},
						error: function(error) {
							_alert('danger', '<b>Failed!</b> ' + error + '.');
						}
					});					
				}
				else
					_alert('danger', '<b>Failed!</b> Parameter check list is empty.');
			}	
			else if (control == 'reset')
				$('div[id=' + id + 'List]').html(backup[id]);				
		}
		else if (id == 'hostLimited') {
			control = $(this).attr('id');
			if (control == 'add') {
				$('div[id=' + id + 'List]').append('<div class="row" style="margin-top: 10px;"><div class="col col-sm-4"><input type="text" class="form-control" value=""></div><div class="col col-md-4"><button id="remove" class="btn btn-sm btn-flat btn-danger" style="margin-top: 3px;"><i class="fa fa-ban"></i>&nbsp; Remove</button></div></div>');
			}	
			else if (control == 'save') {
				var hostLimited = []; var check = [];
				$('div[id=' + id + 'List]').children('div[class=row]').children('div').children('input').each(function() {
					if ($(this).length > 0 && $(this).val() != '')
						hostLimited.push($(this).val());
				});
				if (!_compareArray(hostLimited, check)) {
					var payload = {'hostLimited': hostLimited}
					$.ajax({
						url: 'cpanel.php',
						method: 'POST',
						data: 'type=savedata&t=config&d=' + JSON.stringify(payload),
						beforeSend: function() {
							$('div[id=' + id + ']').slideUp('slow');
						},
						success: function() {
							_alert('success', '<b>Successed!</b> Update host limited list.');
						},
						error: function(error) {
							_alert('danger', '<b>Failed!</b> ' + error + '.');
						}
					});					
				}
				else
					_alert('danger', '<b>Failed!</b> Host limited list is empty.');
			}	
			else if (control == 'reset')
				$('div[id=' + id + 'List]').html(backup[id]);					
		}
		else if (id == 'sizeLimited') {
			control = $(this).attr('id');
			if (control == 'add') {
				var random = Math.floor((Math.random() * 1000) + 1);
				$('div[id=' + id + 'List]').append('<div class="row" style="margin-top: 10px;"><div class="col col-sm-3"><input id="' + random + '" type="text" class="form-control" value=""></div><div class="col col-sm-2"><input name="' + random + '" type="text" class="form-control" value=""></div><div class="col col-md-2"><button id="remove" class="btn btn-sm btn-flat btn-danger" style="margin-top: 3px;"><i class="fa fa-ban"></i>&nbsp; Remove</button></div></div>');
			}
			else if (control == 'save') {
				var sizeLimited = {}; var check = {};
				$('div[id=' + id + 'List]').children('div[class=row]').each(function() {
					$(this).children('div').children('input').each(function() {
						if ($(this).length > 0 && $(this).val() != '') {
				        	var auth = $(this).attr('id');
							if (typeof auth !== 'undefined' && auth != 'remove' && auth != 'vn' && auth != 'us') {
								if ($(this).val() == 'default') {
									if ($('input[id=vn]').val() != '' && $('input[id=us]').val() != '') {
					                	sizeLimited['default'] = {};
										sizeLimited['default']['vn'] = _convertSize($('input[id=vn]').val());
										sizeLimited['default']['us'] = _convertSize($('input[id=us]').val());					
									}
									else {
										_alert('danger', '<b>Failed!</b> Size of default is empty.');
										return false;
					                }
								}
								else 
									if ($('input[name=' + auth + ']').val() != '') 
										sizeLimited[$(this).val()] = _convertSize($('input[name=' + auth + ']').val());
				            }
						}
					});
				});
				if (!_compareArray(sizeLimited, check)) {
					var payload = {'sizeLimited': {'member': sizeLimited}}
					$.ajax({
						url: 'cpanel.php',
						method: 'POST',
						data: 'type=savedata&t=config&d=' + JSON.stringify(payload),
						beforeSend: function() {
							$('div[id=' + id + ']').slideUp('slow');
						},
						success: function() {
							_alert('success', '<b>Successed!</b> Update size limited for member list.');
						},
						error: function(error) {
							_alert('danger', '<b>Failed!</b> ' + error + '.');
						}
					});					
				}
				else
					_alert('danger', '<b>Failed!</b> Size limited for member list is empty.');
			}
			else if (control == 'reset')
				$('div[id=' + id + 'List]').html(backup[id]);		
		}
		else if (id == 'bandwithLimited') {
			control = $(this).attr('id');
			if (control == 'add') {
				var random = Math.floor((Math.random() * 1000) + 1);
				$('div[id=' + id + 'List]').append('<div class="row" style="margin-top: 10px;"><div class="col col-sm-3"><input id="' + random + '" type="text" class="form-control" value=""></div><div class="col col-sm-2"><input name="' + random + '" type="text" class="form-control" value=""></div><div class="col col-md-2"><button id="remove" class="btn btn-sm btn-flat btn-danger" style="margin-top: 3px;"><i class="fa fa-ban"></i>&nbsp; Remove</button></div></div>');
			}
			else if (control == 'save') {
				var bandwithLimited = {}; var check = {};
				$('div[id=' + id + 'List]').children('div[class=row]').each(function() {
					$(this).children('div').children('input').each(function() {
						if ($(this).length > 0 && $(this).val() != '') {
				        	var auth = $(this).attr('id');
							if (typeof auth !== 'undefined' && auth != 'remove') {
								if ($(this).val() == 'default') {
									if ($('input[name=default]').val() != '') 
										bandwithLimited['default'] = _convertSize($('input[name=default]').val());				
									else {
										_alert('danger', '<b>Failed!</b> Size of default is empty.');
										return false;
					                }
								}
								else 
									if ($('input[name=' + auth + ']').val() != '') 
										bandwithLimited[$(this).val()] = _convertSize($('input[name=' + auth + ']').val());
				            }
						}
					});
				});
				if (!_compareArray(bandwithLimited, check)) {
					var payload = {'bandwithLimited': {'member': bandwithLimited}}
					$.ajax({
						url: 'cpanel.php',
						method: 'POST',
						data: 'type=savedata&t=config&d=' + JSON.stringify(payload),
						beforeSend: function() {
							$('div[id=' + id + ']').slideUp('slow');
						},
						success: function() {
							_alert('success', '<b>Successed!</b> Update bandwith limited for member list.');
						},
						error: function(error) {
							_alert('danger', '<b>Failed!</b> ' + error + '.');
						}
					});					
				}
				else
					_alert('danger', '<b>Failed!</b> Bandwith limited for member list is empty.');
			}
			else if (control == 'reset')
				$('div[id=' + id + 'List]').html(backup[id]);
		}
		else if (id == 'sizeVipLimited') {
			control = $(this).attr('id');
			if (control == 'add') {
				var random = Math.floor((Math.random() * 1000) + 1);
				$('div[id=' + id + 'List]').append('<div class="row" style="margin-top: 10px;"><div class="col col-sm-3"><input id="' + random + '" type="text" class="form-control" value=""></div><div class="col col-sm-2"><input name="' + random + '" type="text" class="form-control" value=""></div><div class="col col-md-2"><button id="remove" class="btn btn-sm btn-flat btn-danger" style="margin-top: 3px;"><i class="fa fa-ban"></i>&nbsp; Remove</button></div></div>');
			}
			else if (control == 'save') {
				var sizeVipLimited = {}; var check = {};
				$('div[id=' + id + 'List]').children('div[class=row]').each(function() {
					$(this).children('div').children('input').each(function() {
						if ($(this).length > 0 && $(this).val() != '') {
				        	var auth = $(this).attr('id');
							if (typeof auth !== 'undefined' && auth != 'remove' && auth != 'default') {
								if ($('input[name=' + auth + ']').val() != '') 
									sizeVipLimited[$(this).val()] = _convertSize($('input[name=' + auth + ']').val());
				            }
						}
					});
				});
				if (!_compareArray(sizeVipLimited, check)) {
					var payload = {'sizeLimited': {'vip': sizeVipLimited}}
					$.ajax({
						url: 'cpanel.php',
						method: 'POST',
						data: 'type=savedata&t=config&d=' + JSON.stringify(payload),
						beforeSend: function() {
							$('div[id=' + id + ']').slideUp('slow');
						},
						success: function() {
							_alert('success', '<b>Successed!</b> Update size limited for vip list.');
						},
						error: function(error) {
							_alert('danger', '<b>Failed!</b> ' + error + '.');
						}
					});					
				}
				else
					_alert('danger', '<b>Failed!</b> Size limited for vip list is empty.');
			}
			else if (control == 'reset')
				$('div[id=' + id + 'List]').html(backup[id]);
		}
		else if (id == 'bandwithVipLimited') {
			control = $(this).attr('id');
			if (control == 'add') {
				var random = Math.floor((Math.random() * 1000) + 1);
				$('div[id=' + id + 'List]').append('<div class="row" style="margin-top: 10px;"><div class="col col-sm-3"><input id="' + random + '" type="text" class="form-control" value=""></div><div class="col col-sm-2"><input name="' + random + '" type="text" class="form-control" value=""></div><div class="col col-md-2"><button id="remove" class="btn btn-sm btn-flat btn-danger" style="margin-top: 3px;"><i class="fa fa-ban"></i>&nbsp; Remove</button></div></div>');
			}
			else if (control == 'save') {
				var bandwithVipLimited = {}; var check = {};
				$('div[id=' + id + 'List]').children('div[class=row]').each(function() {
					$(this).children('div').children('input').each(function() {
						if ($(this).length > 0 && $(this).val() != '') {
				        	var auth = $(this).attr('id');
							if (typeof auth !== 'undefined' && auth != 'remove' && auth != 'default') {
								if ($('input[name=' + auth + ']').val() != '') 
									bandwithVipLimited[$(this).val()] = _convertSize($('input[name=' + auth + ']').val());
				            }
						}
					});
				});
				if (!_compareArray(bandwithVipLimited, check)) {
					var payload = {'bandwithLimited': {'vip': bandwithVipLimited}}
					$.ajax({
						url: 'cpanel.php',
						method: 'POST',
						data: 'type=savedata&t=config&d=' + JSON.stringify(payload),
						beforeSend: function() {
							$('div[id=' + id + ']').slideUp('slow');
						},
						success: function() {
							_alert('success', '<b>Successed!</b> Update size limited for vip list.');
						},
						error: function(error) {
							_alert('danger', '<b>Failed!</b> ' + error + '.');
						}
					});					
				}
				else
					_alert('danger', '<b>Failed!</b> Size limited for vip list is empty.');
			}
			else if (control == 'reset')
				$('div[id=' + id + 'List]').html(backup[id]);
		}
		else {
			id = $(this).attr('id');
			if (id == 'remove')
				$(this).parent().parent().remove();
			else if (id == 'run') {
				var name = $('input[id=nameBot]').val();
				var pass = $('input[id=passBot]').val();
				if (name != '' && pass != '') {
					$.ajax({
						url: 'cpanel.php',
						method: 'POST',
						data: 'type=getkey&n=' + name + '&p=' + pass,
						beforeSend: function() {
							$('div[id=getKey]').slideUp('slow');
						},
						success: function(data) {
							if (data != 'error') {
								$('input[id=keyBot]').val(data);
								_alert('success', '<b>Successed!</b> Get key.');
							}
							else 
								_alert('danger', '<b>Failed!</b> Username or Password is not correct.');
						},
						error: function(error) {
							_alert('danger', '<b>Failed!</b> ' + error + '.');
						}
					});
				}
				else
					_alert('danger', '<b>Failed!</b> Name or Password is empty.');
			}
		}
	});
});