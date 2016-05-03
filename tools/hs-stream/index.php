<?php

$valid_passwords = array ("pcesports" => "123890");
$valid_users = array_keys($valid_passwords);

$user = $_SERVER['PHP_AUTH_USER'];
$pass = $_SERVER['PHP_AUTH_PW'];

$validated = (in_array($user, $valid_users)) && ($pass == $valid_passwords[$user]);

if (!$validated) {
	header('WWW-Authenticate: Basic realm="FK off :)"');
	header('HTTP/1.0 401 Unauthorized');
	die ("Not authorized");
}

if (substr($_SERVER['HTTP_HOST'], 0, 3) == 'dev') {
	$env = 'dev';
}
else {
	$env = 'direct';
}
?>

<!doctype html>
<html>
<head>
    <title>HS icon swapper</title>
    <style>
    body { font: 13px Helvetica, Arial; }
    .status {position: absolute; top: 1%; right: 1%; background-color: #ddd; padding: 5px; border-radius: 4px; opacity: 0.5; color: #000;}
    .player1, .player2 { float: left; padding: 20px; margin: 20px; }
    .player1 {border: 1px solid blue;}
    .player2 {border: 1px solid red;}
    .ban {color: #900; font-style: italic;}
    label { float: left; }
    input[type="checkbox"] { float: right; }
    .clear { clear: both; }
    </style>
</head>
<body>
    <div class="status"></div>

    <div class="player1">
        <select class="list">
            <option>-</option>
        </select>
        <div class="name">-</div>
        <div class="hero1"><label>-</label><input type="checkbox" /></div>
        <div class="clear"></div>
        <div class="hero2"><label>-</label><input type="checkbox" /></div>
        <div class="clear"></div>
        <div class="hero3"><label>-</label><input type="checkbox" /></div>
        <div class="clear"></div>
        <div class="ban"><label>-</label></div>
    </div>

    <div class="player2">
        <select class="list">
            <option>-</option>
        </select>
        <div class="name">-</div>
        <div class="hero1"><label>-</label><input type="checkbox" /></div>
        <div class="clear"></div>
        <div class="hero2"><label>-</label><input type="checkbox" /></div>
        <div class="clear"></div>
        <div class="hero3"><label>-</label><input type="checkbox" /></div>
        <div class="clear"></div>
        <div class="ban"><label>-</label></div>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
    <script src="https://cdn.socket.io/socket.io-1.3.7.js"></script>
    <script>
        var socket = io('http://<?=$env?>.pcesports.com:3008');

        var heroes = [
            '',
            'warrior',
            'hunter',
            'mage',
            'warlock',
            'shaman',
            'rogue',
            'druid',
            'paladin',
            'priest'
        ];

        //Clearing browser saved data for checkboxes
        $('.list option').prop('selected', false);
        $('input[type="checkbox"]').prop('checked', false);

        socket
        .on('status', function(msg) {
            $('.status').text(msg);
        })
        .on('updateUserList', function(msg) {
            //data[0] = player number (1 or 2)
            //data[1] = player nickname
            //data[2-4] = player heroes ID
            //data[5-7] = player won/lost data for each hero
            //data[2] hero = data[5] won/lost data
            //data[3] hero = data[6] won/lost data
            //data[4] hero = data[7] won/lost data
            //data[8] = banned hero

            data = $.parseJSON(msg);
            var html = '<option value="-;-;-;-">-</option>';
            $.each(data, function(k,v) {
                html += '<option value="'+v.name+';'+heroes[v.contact_info.hero1]+';'+heroes[v.contact_info.hero2]+';'+heroes[v.contact_info.hero3]+';'+heroes[v.contact_info.ban]+'" >'+v.name+'</option>';
            });
            $('.list').html(html);
        })
        .on('userSelectedData', function(msg) {
            //Clearing data
            $('.list option').prop('selected', false);
            $('input[type="checkbox"]').prop('checked', false);
            $('.name').html('-');
            for (i=1; i<=3; ++i) {
                $('.hero'+i+' label').html('-');
            }
            $('.ban label').html('-');

            $.each(msg, function(k ,v) {
                if (v !== null) {
                    //Updating select option
                    $('.player'+v[0]+' .list option:contains("'+v[1]+'")').prop('selected', true).attr('selected', true);
                    
                    //Update name text
                    $('.player'+v[0]+' .name').html(v[1]);
                    
                    //Update labels for heroes
                    for (i=1; i<=3; ++i) {
                        $('.player'+v[0]+' .hero'+i+' label').html(v[i+1]);
                    }

                    //Update heroes won/lost status in checkboxes
                    for (i=1; i<=3; ++i) {
                        if (v[4+i] === true) {
                            $('.player'+v[0]+' .hero'+i+' input[type="checkbox"]').prop('checked', true).attr('checked', true);
                        }
                    }

                    //Update ban label
                    if (v[8] != 'undefined') {
                        $('.player'+v[0]+' .ban label').html(v[8]);
                    }
                    else {
                        $('.player'+v[0]+' .ban label').html('-');   
                    }
                }
            });
        });

        $('.list').on('change', function() {
            $.each($('input[type="checkbox"]'), function(k,v) {
                $(v).attr('checked', false);
            });

            updateIcons($(this).parent());
        });

        $('input[type="checkbox"').on('change', function() {
            updateIcons($(this).parent().parent());
        })

        function updateIcons(element) {
            var data = element.find('.list').val().split(';');

            var ban = data[4];

            element.find('.name').text(data[0]);

            for (i=1; i<=3; ++i) {
                element.find('.hero'+i+' label').html(data[i]);
            }

            if (data[1] == '-') {
                if (element.hasClass('player1')) {
                    socket.emit('cleanUser', 1);
                }
                else {
                    socket.emit('cleanUser', 2);
                }
                return false;
            }

            if (element.hasClass('player1')) {
                data.unshift(1);
            }
            else {
                data.unshift(2);
            }
        
            for(i=1; i<=3; ++i) {
                data[4+i] = $('.player'+data[0]+' .hero'+i+' input[type="checkbox"').is(':checked');
            }

            data[8] = ban;

            console.log(data);

            socket.emit('updateIcons', data);
        }
    </script>
    </body>
</html>