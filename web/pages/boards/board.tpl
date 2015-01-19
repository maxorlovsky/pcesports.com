<section class="container page boards">

<div class="left-containers">
    <div class="block">
        <?
		if ($row) {
        ?>
        <div class="block-content board">
            <div class="voting" attr-id="<?=$row->id?>">
                <div class="arrow top <?=($row->direction=='plus'?'voted':null)?>"></div>
                <div class="count" id="board_vote_<?=$row->id?>"><?=$row->votes?></div>
                <div class="arrow bottom <?=($row->direction=='minus'?'voted':null)?>"></div>
            </div>
            <a class="category" href="<?=_cfg('href')?>/boards/<?=$row->id?>">
                <? if ($row->category != 'general') { ?>
                <img src="<?=_cfg('img').'/'.$row->category?>.png" />
                <? } ?>
            </a>
            <div class="thread">
                <a class="title" href="<?=_cfg('href')?>/boards/<?=$row->id?>"><?=Db::escape_tags($row->title)?></a>
                <div class="clear"></div>
                <div class="date-user-box">
                    <?=t('submitted')?> <?=$row->interval?> <?=t('by')?> 
                    <a class="comment-user" href="<?=_cfg('href')?>/member/<?=$row->name?>">
                        <img class="avatar-block" src="<?=_cfg('avatars')?>/<?=$row->avatar?>.jpg" /><?=$row->name?>
                    </a>
                </div>
                <div class="text">
                    <?=$this->parseText($row->text)?>
                </div>
                <div class="actions">
                    <? if ($row->user_id == $this->data->user->id) { ?>
                        <a class="edit" href="#"><?=t('edit')?></a>
                        <a class="delete" href="#"><?=t('delete')?></a>
                    <? } else { ?>
                        <a class="report" href="#"><?=t('report')?></a>
                    <? } ?>
                </div>
                <div class="actions">
                    <!--<a class="comments-list" href="<?=_cfg('href')?>/boards/<?=$row->id?>"><?=$row->comments?> <?=t('comments')?></a>-->
                    <!--<a class="share" href="#"><?=t('share')?></a>-->
                    <!--<a class="report" href="#"><?=t('report')?></a>-->
                </div>
                <div class="share-box">
                    <div class="addthis_sharing_toolbox"></div>
                </div>
            </div>
            
            <div class="clear"></div>
        </div>
        <?
        }
        ?>
        
        <div class="block-divider"></div>
        
        <div class="comments">
        	<h2><?=t('post_submit')?></h2>
            
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
                
                <a href="javascript:void(0);" class="button" id="submitBoardComment"><?=t('post')?></a>
                
                <input type="hidden" id="module" name="module" value="comment" />
                <input type="hidden" id="id" name="id" value="<?=$row->id?>" />
                <? } else { ?>
                    <a href="javascript:void(0);" class="login"><?=t('login_to_leave_comment')?></a>
                <? } ?>
            </form>
            
            <div class="user-comments">
                <?
                if ($this->comments) {
                    foreach($this->comments as $v) {
                ?>
                    <div class="master">
                        <!--<div class="voting">
                            <div class="arrow top"></div>
                            <div class="count"><?=$v->votes?></div>
                            <div class="arrow bottom"></div>
                        </div>-->
                        <div class="body">
                            <p><?=$v->text?></p>
                            <span class="comment-user">
                                <a href="<?=_cfg('href')?>/member/<?=$v->name?>">
                                    <img class="avatar-block" src="<?=_cfg('avatars')?>/<?=$v->avatar?>.jpg" />
                                    <?=$v->name?>
                                </a>
                            </span>
                            <span class="comment-time">- <?=$v->interval?></span>
                        </div>
                        
                        <div class="clear"></div>
                    </div>
                <?
                    }
                }
                ?>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript" src="//s7.addthis.com/js/300/addthis_widget.js#pubid=ra-4dfdc8015d8f785b"></script>