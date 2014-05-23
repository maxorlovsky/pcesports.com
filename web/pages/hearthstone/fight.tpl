<section class="container page lol">

<div class="left-containers">
	<div class="block">
        <div class="block-header-wrapper">
            <h1 class="bordered">Opponent info</h1>
        </div>
        
        <div class="block-content opponent-info">
            <label>Opponent</label>: <span id="opponentName"></span><br />
            <label>Status</label>: <span id="opponentStatus"></span> (<span id="opponentSec"></span> sec)
        </div>
    </div>
    
    <div class="block">
        <div class="block-header-wrapper">
            <h1 class="bordered">Battle Chat</h1>
        </div>
        
        <div class="block-content">
            <div class="notification">Please don't forget that official language of the chat is <b>English</b>! Be polite.</div>
            <div class="chat">
                <div class="chat-content"></div>
                <div class="chat-input">
                    <input type="text" id="chat-input" />
                    <div id="uploadScreen" class="attach-file" title="Attach file"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?=_cfg('static')?>/js/profiler.js"></script>