function _alert(type, message) {
	$('.alert').addClass('alert-' + type).html(message).slideDown('slow');
	setTimeout(function() { 
		$('.alert').slideUp('slow');
	}, 1000);
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
	var backup = $('tbody').html();
	$(document).on('click', 'div[class=card-body]', function() {
		if (!$('div[class=card-footer]').is(':visible'))
			$('div[class=card-footer]').slideDown('slow');
	});
	$(document).on('click', 'span', function() {
		if ($(this).text() == 'Online')
			$(this).attr('class', 'badge badge-danger').text('Offline');
		else
			$(this).attr('class', 'badge badge-success').text('Online');
	});	
	$(document).on('click', 'button', function() {
		var control = $(this).attr('id');
		if (control == 'removeHost')
			$(this).parent().parent().parent().remove();
		else if (control == 'addHost')
			$(this).parent().parent().parent().before('<tr><td><input class="form-control" value=""></td><td><div class="row"><div class="col col-md-10"><input class="form-control" value=""></div><div class="col col-md-2"><button id="removeServer" class="btn btn-sm btn-flat btn-danger" style="margin-top: 3px;"><i class="fa fa-ban"></i>&nbsp; Remove</button></div></div><div style="margin-top: 10px;"><button id="addServer" class="btn btn-sm btn-flat btn-primary"><i class="fa fa-plus"></i>&nbsp;Add </button></div></td><td><div style="margin-top: 5px;"><span class="badge badge-danger">Offline</span></div></td><td><div class="col col-md-2"><button id="removeHost" class="btn btn-sm btn-flat btn-danger" style="margin-top: 3px;"><i class="fa fa-ban"></i>&nbsp; Remove</button></div></td></tr>');
		else if (control == 'removeServer')
			$(this).parent().parent().remove();
		else if (control == 'addServer') {
			$(this).parent().before('<div class="row" style="margin-top: 10px;"><div class="col col-md-10"><input class="form-control" value=""></div><div class="col col-md-2"><button id="removeServer" class="btn btn-sm btn-flat btn-danger" style="margin-top: 3px;"><i class="fa fa-ban"></i>&nbsp; Remove</button></div></div>');
		}
		else if (control == 'save') {
			var server = {}; var check = {};
			$('tbody').children('tr').each(function() {
				var host = ''; var temp = [];
				$(this).children('td').each(function() {
					var value = $(this).children('input').val();
					if (typeof value !== 'undefined' && value != '') {
						host = value;
						server[value] = {'server': [], 'work': false};
						return;
					}
					if (typeof value === 'undefined' && host != '') {
						$(this).children('div[class=row]').children('div.col.col-md-10').children('input').each(function() {
							if ($(this).length > 0 && $(this).val() != '')
								server[host]['server'].push($(this).val());
						});
						if ($(this).children('div').children('span').text().length > 0 && $(this).children('div').children('span').text() != '' && !_compareArray(server[host]['server'], temp))
							if ($(this).children('div').children('span').text() == 'Online')
								server[host]['work'] = true;
							else if ($(this).children('div').children('span').text() == 'Offline')
								server[host]['work'] = false;
					}
			    });
			});
			if (!_compareArray(server, check)) {
				$.ajax({
					url: 'cpanel.php',
					method: 'POST',
					data: 'type=savedata&t=server&d=' + JSON.stringify(server),
					beforeSend: function() {
						$('div[class=footer]').slideUp('slow');
					},
					success: function() {
						_alert('success', '<b>Successed!</b> Update server.');
					},
					error: function(error) {
						_alert('danger', '<b>Failed!</b> ' + error + '.');
					}
				});
			}
			else
				_alert('danger', '<b>Failed!</b> Empty server.');
		}
		else if (control == 'reset')
			$('tbody').html(backup);
	});
});