<section class="container page home lol boards">

<div class="left-containers">

    <?
    if ($this->streams && _cfg('https') != 1) {
        $i = 0;
    ?>
        <div class="block twitch">
            <div class="block-header-wrapper">
                <h1 class=""><?=t('featured_stream')?></h1>
            </div>
            <? foreach($this->streams as $v) { ?>
                <div id="player" attr-current="<?=$v->name?>">
                    <object type="application/x-shockwave-flash" height="400" width="100%" id="live_embed_player_flash" data="http://www.twitch.tv/widgets/live_embed_player.swf?channel=<?=$v->name?>">
                        <param name="allowFullScreen" value="true" />
                        <param name="allowScriptAccess" value="always" />
                        <param name="allowNetworking" value="all" />
                        <param name="movie" value="http://www.twitch.tv/widgets/live_embed_player.swf" />
                        <param name="flashvars" value="hostname=www.twitch.tv&amp;channel=<?=$v->name?>&amp;auto_play=false&amp;start_volume=25" />
                    </object>
                </div>
            <?
                break;
            }
            ?>
            
            <div class="featured-list">
                <?
                foreach($this->streams as $v) {
                ?>
                    <div class="featured-streamer <?=($i==0?'active':null)?> <?=(isset($v->event)&&$v->event==1?'event':null)?> <?=$v->game?> hint" attr-msg="<?=$v->display_name?>" attr-name="<?=$v->name?>">
                        <div class="image"><img src="http://static-cdn.jtvnw.net/previews-ttv/live_user_<?=$v->name?>-80x45.jpg" /></div>
                        <div class="name"><?=$v->display_name?></div>
                        <div class="viewers"><?=t('viewers')?>: <?=$v->viewers?></div>
                        <div class="clear"></div>
                    </div>
                <?
                    $i = 1;
                }
                ?>
            </div>
            <div class="clear"></div>
        </div>
    <?
    }
    ?>
    
    <? if ($this->slider) { ?>
    <div class="block promo">
        <ul class="bx-wrapper">
        	<? foreach($this->slider as $v) { ?>
            <li><a href="<?=$v[0]?>"><img src="<?=$v[1]?>" /></a></li>
            <? } ?>
        </ul>
    </div>
    <? } ?>

    <div class="block home-tournaments">
        <?
        if ($this->tournamentData) {
            foreach($this->tournamentData as $k => $v) {
        ?>
            <div class="<?=$k?> parent <?=strtolower($v['status'])?>">
                <a href="<?=_cfg('href')?>/<?=$v['link']?>" class="inner">
                    <h1><?=$v['name']?> #<?=$v['id']?></h1>
                    <div class="status"><label><?=$v['status']?></label></div>
                    <div class="prize-pool">
                        <h4><?=t('prize_pool')?></h4>
                        <div><?=$v['prize']?></div>
                    </div>
                    <div class="timing">
                        <h4 class="timer hint" attr-time="<?=intval($v['time'] - time())?>" attr-msg="<?=$this->convertTime($v['time'], 'j M - '.($this->data->user->timestyle==1?'H':'h').':i'.($this->data->user->timestyle!=1?' A':null), 1)?>" <?=($k=='hs'.$this->data->settings['tournament-season-hs']?'attr-br="1"':null)?>><img src="<?=_cfg('img')?>/bx_loader.gif" /></h4>
                    </div>
                    <div class="registered-now">
                        <div><?=$v['teams']?> / <span class="<?=($v['teams']>=$v['max_num']?'red':null)?>"><?=$v['max_num']?></span></div>
                    </div>
                </a>
            </div>
        <?
            }
        }
        ?>
        <div class="clear"></div>
    </div>
    
    <div class="block separate">
        <div class="block-header-wrapper">
            <h1 class=""><?=t('latest_blog')?></h1>
        </div>
        <? if ($this->blog) { ?>
        <div class="block-content news big-block">
        	<div class="add-box">
        		<div class="date"><?=date('M', strtotime($this->blog->added) + $this->timezone)?><br /><?=date('d', strtotime($this->blog->added)+$this->timezone)?></div>
        		<a class="like" href="javascript:void(0);" attr-news-id="<?=$this->blog->id?>">
        			<div class="placeholder">
        				<div class="like-icon <?=($this->blog->active?'active':null)?>"></div>
					</div>
        		</a>
        	</div>
            <? if ($this->blog->extension) { ?>
                <a class="image-holder" href="<?=_cfg('href')?>/blog/<?=$this->blog->id?>">
                    <? if (_cfg('env') == 'dev') { ?>
                        <img src="http://www.pcesports.com/web/uploads/news/big-<?=$this->blog->id?>.<?=$this->blog->extension?>" />
                    <? } else { ?>
                        <img src="<?=_cfg('imgu')?>/news/big-<?=$this->blog->id?>.<?=$this->blog->extension?>" />
                    <? } ?>
                </a>
            <? } ?>
        	<a href="<?=_cfg('href')?>/blog/<?=$this->blog->id?>" class="title"><?=$this->blog->title?></a>
        	<div class="text"><?=$this->blog->value?></div>
        </div>
        <div class="block-content news big-block readmore">
        	<div class="news-info">
				<?=t('added_by')?> <a href="<?=_cfg('href')?>/member/<?=$this->blog->login?>"><?=$this->blog->login?></a>, 
				<span id="news-like-<?=$this->blog->id?>"><?=$this->blog->likes?></span> <?=t('likes')?>,
                <span><?=$this->blog->views?></span> <?=t('views')?>, 
				<span><?=$this->blog->comments?></span> <?=t(($this->blog->comments>1?'comments':'comment'))?>
			</div>
        	<a class="button" href="<?=_cfg('href')?>/blog/<?=$this->blog->id?>"><?=t('read_more')?></a>
        	<div class="clear"></div>
        </div>
        <? } ?>
        
        <div class="block separate">
            <div class="block-header-wrapper">
                <h1 class=""><?=t('last_topics_on_boards')?></h1>
            </div>
            <?
            if ($this->boards) {
                foreach($this->boards as $v) {
            ?>
            <div class="block-content board" attr-id="<?=$v->id?>">
                <div class="voting">
                    <div class="arrow top <?=($v->direction=='plus'?'voted':null)?>"></div>
                    <div class="count" id="board_vote_<?=$v->id?>"><?=$v->votes?></div>
                    <div class="arrow bottom <?=($v->direction=='minus'?'voted':null)?>"></div>
                </div>
                <a class="category" href="<?=_cfg('href')?>/boards/<?=$v->id?>">
                    <img src="<?=_cfg('img').'/'.$v->category?>.png" />
                </a>
                <div class="thread">
                    <a class="title" href="<?=_cfg('href')?>/boards/<?=$v->id?>"><?=$v->title?></a>
                    <div class="clear"></div>
                    <div class="date-user-box">
                        <?=t('submitted')?> <?=$v->interval?>
                        <?
                        if ($v->edited==1 && $v->status != 1) {
                            echo ' <i>('.t('edited').')</i>';
                        }
                        else if ($v->status == 1) {
                            echo ' <span class="deleted">('.t('deleted').') </span>';
                        }
                        ?> 
                        <?=t('by')?> 
                        <a class="comment-user" href="<?=_cfg('href')?>/member/<?=$v->name?>">
                            <img class="avatar-block" src="<?=_cfg('avatars')?>/<?=$v->avatar?>.jpg" /><?=$v->name?>
                        </a>
                    </div>
                    <div class="actions">
                        <a class="comments-list" href="<?=_cfg('href')?>/boards/<?=$v->id?>"><?=$v->comments?> <?=t(($v->comments>1?'comments':'comment'))?></a>
                        <!--<a class="share" href="#"><?=t('share')?></a>-->
                        <? if ($v->user_id == $this->data->user->id && $v->status != 1) { ?>
                            <a class="edit" href="<?=_cfg('href')?>/boards/submit/<?=$v->id?>"><?=t('edit')?></a>
                            <a class="delete" href="#" attr-msg="<?=t('sure_to_delete_message')?>"><?=t('delete')?></a>
                        <? } else if ($v->status != 1) { ?>
                            <a class="report" href="#" attr-msg="<?=t('sure_to_report_message')?>"><?=t('report')?></a>
                        <? } ?>
                    </div>
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