function _alert(type, message) {
	$('.alert').addClass('alert-' + type).html(message).slideDown('slow');
	setTimeout(function() { 
		$('.alert').slideUp('slow');
	}, 1000);
}

jQuery(document).ready(function() {
	$('[data-toggle="tooltip"]').tooltip();
	$('#password').blur(function() {
		if ($('#password').val() == '')
			_alert('danger', '<b>Empty!</b> password.');

	});
	$(document).on('click', 'button', function() {
		var password = $('#password').val();
		if (password != '') {
			$.ajax({
				url: 'cpanel.php',
				method: 'POST',
				data: 'type=login&password=' + password,
				success: function() {
					_alert('success', '<b>Succssed!</b> login.');
					setTimeout(function() { 
						location.reload();
					}, 1000);
				},
				error: function(error) {
					_alert('danger', '<b>Failed!</b> ' + error + '.');
				}
			});
		}
	});
});