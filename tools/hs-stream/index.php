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
    .player1 div,
    .player2 div {
        margin-bottom: 5px;
    }
    .ban {color: #900; font-style: italic;}
    label { float: left; margin-right: 5px; }
    input[type="checkbox"] { float: right; }
    input[type="text"] { padding: 2px; box-sizing: border-box; }
    .clear { clear: both; }
    .ban select { margin-left: 20px; }

    #update { margin-left: 190px;}
    </style>
</head>
<body>
    <div class="status"></div>

    <div class="player1">
        <input type="text" class="name" value="-" />
        <div class="hero1">
            <label>Class 1 - </label>
            <select class="list">
                <option value="0">-</option>
                <option value="1">warrior</option>
                <option value="2">hunter</option>
                <option value="3">mage</option>
                <option value="4">warlock</option>
                <option value="5">shaman</option>
                <option value="6">rogue</option>
                <option value="7">druid</option>
                <option value="8">paladin</option>
                <option value="9">priest</option>
            </select>
            <input type="checkbox" />
        </div>
        <div class="clear"></div>
        <div class="hero2">
            <label>Class 2 - </label>
            <select class="list">
                <option value="0">-</option>
                <option value="1">warrior</option>
                <option value="2">hunter</option>
                <option value="3">mage</option>
                <option value="4">warlock</option>
                <option value="5">shaman</option>
                <option value="6">rogue</option>
                <option value="7">druid</option>
                <option value="8">paladin</option>
                <option value="9">priest</option>
            </select>
            <input type="checkbox" />
        </div>
        <div class="clear"></div>
        <div class="hero3">
            <label>Class 3 - </label>
            <select class="list">
                <option value="0">-</option>
                <option value="1">warrior</option>
                <option value="2">hunter</option>
                <option value="3">mage</option>
                <option value="4">warlock</option>
                <option value="5">shaman</option>
                <option value="6">rogue</option>
                <option value="7">druid</option>
                <option value="8">paladin</option>
                <option value="9">priest</option>
            </select>
            <input type="checkbox" />
        </div>
        <div class="clear"></div>
        <div class="ban">
            <label>Ban - </label>
            <select class="list">
                <option value="0">-</option>
                <option value="1">warrior</option>
                <option value="2">hunter</option>
                <option value="3">mage</option>
                <option value="4">warlock</option>
                <option value="5">shaman</option>
                <option value="6">rogue</option>
                <option value="7">druid</option>
                <option value="8">paladin</option>
                <option value="9">priest</option>
            </select>
        </div>
    </div>

    <div class="player2">
        <input type="text" class="name" value="-" />
        <div class="hero1">
            <label>Class 1 - </label>
            <select class="list">
                <option value="0">-</option>
                <option value="1">warrior</option>
                <option value="2">hunter</option>
                <option value="3">mage</option>
                <option value="4">warlock</option>
                <option value="5">shaman</option>
                <option value="6">rogue</option>
                <option value="7">druid</option>
                <option value="8">paladin</option>
                <option value="9">priest</option>
            </select>
            <input type="checkbox" />
        </div>
        <div class="clear"></div>
        <div class="hero2">
            <label>Class 2 - </label>
            <select class="list">
                <option value="0">-</option>
                <option value="1">warrior</option>
                <option value="2">hunter</option>
                <option value="3">mage</option>
                <option value="4">warlock</option>
                <option value="5">shaman</option>
                <option value="6">rogue</option>
                <option value="7">druid</option>
                <option value="8">paladin</option>
                <option value="9">priest</option>
            </select>
            <input type="checkbox" />
        </div>
        <div class="clear"></div>
        <div class="hero3">
            <label>Class 3 - </label>
            <select class="list">
                <option value="0">-</option>
                <option value="1">warrior</option>
                <option value="2">hunter</option>
                <option value="3">mage</option>
                <option value="4">warlock</option>
                <option value="5">shaman</option>
                <option value="6">rogue</option>
                <option value="7">druid</option>
                <option value="8">paladin</option>
                <option value="9">priest</option>
            </select>
            <input type="checkbox" />
        </div>
        <div class="clear"></div>
        <div class="ban">
            <label>Ban - </label>
            <select class="list">
                <option value="0">-</option>
                <option value="1">warrior</option>
                <option value="2">hunter</option>
                <option value="3">mage</option>
                <option value="4">warlock</option>
                <option value="5">shaman</option>
                <option value="6">rogue</option>
                <option value="7">druid</option>
                <option value="8">paladin</option>
                <option value="9">priest</option>
            </select>
        </div>
    </div>

    <div class="clear"></div>

    <button id="update">Update stream data</button>

    <script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
    <script src="//cdn.socket.io/socket.io-1.3.7.js"></script>
    <script>
        var socket = io('//<?=$env?>.pcesports.com:3008');

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
        .on('updateUserList', function(users) {

            for(i = 1; i <= 2; ++i) {
                if (i == 1) {
                    data = users.player1;
                }
                else {
                    data = users.player2;
                }

                $('.player'+i+' .name').val(data.name);
                $('.player'+i+' .hero1 .list').val(data.class[0]);
                $('.player'+i+' .hero2 .list').val(data.class[1]);
                $('.player'+i+' .hero3 .list').val(data.class[2]);
                $('.player'+i+' .hero1 input[type="checkbox"').prop('checked', data.classStatus[0]).attr('checked', data.classStatus[0]);
                $('.player'+i+' .hero2 input[type="checkbox"').prop('checked', data.classStatus[1]).attr('checked', data.classStatus[1]);
                $('.player'+i+' .hero3 input[type="checkbox"').prop('checked', data.classStatus[2]).attr('checked', data.classStatus[2]);
                $('.player'+i+' .ban .list').val(data.ban);
            }
        });

        $('#update').on('click', function() {
            var userData = {
                player1: {
                    name: $('.player1 .name').val(),
                    class: [
                        $('.player1 .hero1 .list').val(),
                        $('.player1 .hero2 .list').val(),
                        $('.player1 .hero3 .list').val()
                    ],
                    classStatus: [
                        $('.player1 .hero1 input[type="checkbox"').is(':checked'),
                        $('.player1 .hero2 input[type="checkbox"').is(':checked'),
                        $('.player1 .hero3 input[type="checkbox"').is(':checked')
                    ],
                    ban: $('.player1 .ban .list').val()
                },
                player2: {
                    name: $('.player2 .name').val(),
                    class: [
                        $('.player2 .hero1 .list').val(),
                        $('.player2 .hero2 .list').val(),
                        $('.player2 .hero3 .list').val()
                    ],
                    classStatus: [
                        $('.player2 .hero1 input[type="checkbox"').is(':checked'),
                        $('.player2 .hero2 input[type="checkbox"').is(':checked'),
                        $('.player2 .hero3 input[type="checkbox"').is(':checked')
                    ],
                    ban: $('.player2 .ban .list').val()
                }
            };

            socket.emit('updateData', userData);
        });
    </script>
    </body>
</html>