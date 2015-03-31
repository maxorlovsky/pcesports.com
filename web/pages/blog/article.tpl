<section class="container page article">

<div class="left-containers">
    <div class="block">
        <div class="block-header-wrapper">
            <h1 class=""><?=$this->news->title?> | <?=t('blog')?></h1>
        </div>
        <div class="block-content news big-block">
        	<div class="add-box">
        		<div class="date"><?=date('M', strtotime($this->news->added)+$this->data->user->timezone)?><br /><?=date('d', strtotime($this->news->added)+$this->data->user->timezone)?></div>
        		<a class="like" href="javascript:void(0);" attr-news-id="<?=$this->news->id?>">
        			<div class="placeholder">
        				<div class="like-icon <?=($this->news->active?'active':null)?>"></div>
					</div>
        		</a>
        	</div>
        	<div class="image-holder">
                <? if ($this->news->extension) { ?>
                    <img src="<?=_cfg('imgu')?>/blog/big-<?=$this->news->id?>.<?=$this->news->extension?>" />
                <? } ?>
            </div>
        	<div class="text"><?=$this->news->value?></div>
        </div>
        <div class="block-content news big-block readmore">
        	<div class="news-info">
				<?=t('added_by')?> <a href="<?=_cfg('href')?>/member/<?=$this->news->login?>"><?=$this->news->login?></a>, 
				<span id="news-like-<?=$this->news->id?>"><?=$this->news->likes?></span> <?=t('likes')?>,
                <span><?=$this->news->views?></span> <?=t('views')?>, 
                <span id="comments-count"><?=$this->news->comments?></span> <?=t('comments')?>
			</div>
            <div class="news-share">
                <div class="addthis_sharing_toolbox"></div>
            </div>
        	<div class="clear"></div>
        </div>
        <div class="block-divider"></div>
        <div class="comments">
        	<h2><?=t('leave_comments')?></h2>
        	<!-- <div class="disabled">Disabled</div> -->
            
            <form class="leave-comment">
                <? if ($this->logged_in) { ?>
                <div id="error"><p></p></div>
                
                <div class="formatting">
                    <div class="formbut b" title="Bold"></div>
                    <div class="formbut i" title="Italic"></div>
                    <div class="formbut s" title="Line through"></div>
                    <div class="formbut link" title="Link"></div>
                    <div class="formbut q" title="Quote"></div>
                    <div class="formbut list" title="List"></div>
                    <div class="clear"></div>
                </div>
                <div class="fields">
                    <textarea name="msg" id="msg" placeholder="Leave a comment"></textarea>
                </div>
                
                <a href="javascript:void(0);" class="button" id="submitComment"><?=t('post')?></a>
                
                <input type="hidden" id="module" name="module" value="news" />
                <input type="hidden" id="id" name="id" value="<?=$this->news->id?>" />
                <? } else { ?>
                    <a href="javascript:void(0);" class="login"><?=t('login_to_leave_comment')?></a>
                <? } ?>
            </form>
            
            <div class="user-comments"></div>
        </div>
    </div>
</div>

<script>
$(document).ready(function(){
    PC.getNewsComments(<?=$this->news->id?>);
    
    if (window.location.hash == '#comments') {
        $('html, body').animate({scrollTop: $('.comments').offset().top}, 1000);
    }
});
</script>

<script type="text/javascript" src="//s7.addthis.com/js/300/addthis_widget.js#pubid=ra-4dfdc8015d8f785b"></script>