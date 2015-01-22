<section class="container page boards">

<div class="left-containers">
    <div class="block">
        <?
		if ($row) {
        ?>
        <div class="block-content board" attr-id="<?=$row->id?>">
            <div class="voting">
                <div class="arrow top <?=($row->direction=='plus'?'voted':null)?>"></div>
                <div class="count" id="board_vote_<?=$row->id?>"><?=$row->votes?></div>
                <div class="arrow bottom <?=($row->direction=='minus'?'voted':null)?>"></div>
            </div>
            <a class="category" href="<?=_cfg('href')?>/boards/<?=$row->id?>">
                <img src="<?=_cfg('img').'/'.$row->category?>.png" />
            </a>
            <div class="thread">
                <a class="title" href="<?=_cfg('href')?>/boards/<?=$row->id?>"><?=Db::escape_tags($row->title)?></a>
                <div class="clear"></div>
                <div class="date-user-box">
                    <?=t('submitted')?> <?=$row->interval?> <?=($row->edited==1&&$row->status!=1?' <i>('.t('edited').')</i>':null)?> <?=t('by')?> 
                    <a class="comment-user" href="<?=_cfg('href')?>/member/<?=$row->name?>">
                        <img class="avatar-block" src="<?=_cfg('avatars')?>/<?=$row->avatar?>.jpg" /><?=$row->name?>
                    </a>
                </div>
                <div class="text">
                    <?
                    if ($row->status != 1) {
                        echo $this->parseText($row->text);
                    }
                    else {
                        echo '<span class="deleted">'.t('deleted').'</span>';
                    }
                    ?>
                </div>
                <div class="actions">
                    <? if ($row->user_id == $this->data->user->id && $row->status != 1) { ?>
                        <a class="edit" href="<?=_cfg('href')?>/boards/submit/<?=$row->id?>"><?=t('edit')?></a>
                        <a class="delete" href="#" attr-msg="<?=t('sure_to_delete_message')?>"><?=t('delete')?></a>
                    <? } else if ($row->status != 1) { ?>
                        <a class="report" href="#" attr-msg="<?=t('sure_to_report_message')?>"><?=t('report')?></a>
                    <? } ?>
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
        	<h2><?=t('leave_comment')?></h2>
            
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
                    <textarea name="msg" id="msg" placeholder="<?=t('reply_text')?>"></textarea>
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
                    <div class="master" attr-id="<?=$v->id?>">
                        <!--<div class="voting">
                            <div class="arrow top"></div>
                            <div class="count"><?=$v->votes?></div>
                            <div class="arrow bottom"></div>
                        </div>-->
                        <div class="body">
                            <p>
                            <?
                            if ($v->status != 1) {
                                echo $this->parseText($v->text);
                            }
                            else {
                                echo '<span class="deleted">'.t('deleted').'</span>';
                            }
                            ?>
                            </p>
                            <span class="comment-user">
                                <a href="<?=_cfg('href')?>/member/<?=$v->name?>">
                                    <img class="avatar-block" src="<?=_cfg('avatars')?>/<?=$v->avatar?>.jpg" />
                                    <?=$v->name?>
                                </a>
                            </span>
                            <span class="comment-time">- <?=$v->interval?></span> 
                            <span class="deleted edited <?=($v->edited==0||$v->status==1?'hidden':null)?>">(<?=t('edited')?>)</span>
                        </div>
                        <div class="clear"></div>
                        
                        <div class="actions">
                            <? if ($v->user_id == $this->data->user->id && $v->status != 1) { ?>
                                <a class="edit" href="javascript:void(0);"><?=t('edit')?></a>
                                <a class="delete" href="#" attr-msg="<?=t('sure_to_delete_message')?>"><?=t('delete')?></a>
                                
                                <div class="edit-text">
                                    <textarea><?=$v->text?></textarea>
                                    <div id="error"><p></p></div>
                                    <a href="javascript:void(0);" class="button" id="editComment"><?=t('edit')?></a>
                                    <a href="javascript:void(0);" id="closeEditComment"><?=t('cancel')?></a>
                                </div>
                                
                            <? } else if ($v->status != 1) { ?>
                                <a class="report" href="#" attr-msg="<?=t('sure_to_report_message')?>"><?=t('report')?></a>
                            <? } ?>
                        </div>
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