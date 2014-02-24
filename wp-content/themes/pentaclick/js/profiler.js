$(document).ready( function(){
	$('.chat-input').on('click', function() {
	   $('#chat-input').focus();
	});
    
    $('#chat-input').on('keyup', function(e) {
        if (!e) {
            e = window.event;
    	}

        /*if (e.keyCode == 13 && e.shiftKey) {
    		return true;
        }
        else*/
        
        if (e.keyCode == 13) {
            var text = $(this).val();
            $(this).val('');
            var query = {
                type: 'POST',
                dataType: 'json',
                data: {
                    control: 'chat',
                    action: 'send',
                    post: 'tId='+tId+'&text='+text
                },
                success: function(answer) {
                    $('.chat-content').html(answer.html);
                }
            }
            ajax(query);
        }
    });
    
    $('#leave').on('click', function() {
        if (!confirm(_('sure_to_quit'))) {
            return false;
        }
        
        var query = {
            type: 'POST',
            dataType: 'json',
            data: {
                control: 'leave',
                post: 'tId='+tId+'&code='+code
            },
            success: function(answer) {
                if (answer.ok == 1) {
                    alert(answer.message);
                    window.location = site;
                }
                else {
                    alert('Error');
                    console.log(answer);
                }
            }
        }
        ajax(query);
    });
});