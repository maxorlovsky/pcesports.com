<section class="container page home lol">

<div class="left-containers">

    <?
    if ($this->streams) {
        $i = 0;
    ?>
        <div class="block twitch">
            <div class="block-header-wrapper">
                <h1 class=""><?=t('featured_stream')?></h1>
            </div>
            <? foreach($this->streams as $v) { ?>
                <div id="player" attr-current="<?=$v->name?>">
                    <object type="application/x-shockwave-flash" height="400" width="100%" id="live_embed_player_flash" data="https://www.twitch.tv/widgets/live_embed_player.swf?channel=<?=$v->name?>">
                        <param name="allowFullScreen" value="true" />
                        <param name="allowScriptAccess" value="always" />
                        <param name="allowNetworking" value="all" />
                        <param name="movie" value="https://www.twitch.tv/widgets/live_embed_player.swf" />
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
                        <div class="image"><img src="https://static-cdn.jtvnw.net/previews-ttv/live_user_<?=$v->name?>-80x45.jpg" /></div>
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
                    <h1><?=$v['name']?></h1>
                    <div class="status"><label><?=$v['status']?></label></div>
                    <div class="prize-pool">
                        <h4><?=t('prize_pool')?></h4>
                        <div><?=$v['prize']?></div>
                    </div>
                    <? if ($v['status'] != 'ended') { ?>
                    <div class="timing">
                        <h4 class="timer hint" attr-time="<?=intval($v['time'] - time())?>" attr-msg="<?=$this->convertTime($v['time'], 'j M - '.($this->data->user->timestyle==1?'H':'h').':i'.($this->data->user->timestyle!=1?' A':null), 1)?>" <?=($k=='hs'.$this->data->settings['tournament-season-hs']?'attr-br="1"':null)?>><img src="<?=_cfg('img')?>/bx_loader.gif" /></h4>
                    </div>
                    <? } ?>
                    <div class="registered-now">
                        <div>Max. slots: <?=$v['max_num']?></div>
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
            <h1 class=""><?=t('latest_blogs')?></h1>
        </div>

        <div class="block-content news">
        <?
        if ($this->blog) {
            foreach($this->blog as $v) {
        ?>
            <div class="small-block">
                <div class="image-holder">
                    <a href="<?=_cfg('href')?>/blog/<?=$v->id?>">
                    <? if ($v->extension) { ?>
                        <? if (_cfg('env') != 'prod') { ?>
                            <img src="http://www.pcesports.com/web/uploads/news/small-<?=$v->id?>.<?=$v->extension?>" />
                        <? } else { ?>
                            <img src="<?=_cfg('imgu')?>/news/small-<?=$v->id?>.<?=$v->extension?>" />
                        <? } ?>
                    <? } else { ?>
                        <p><?=t('no_image')?></p>
                    <? } ?>
                    </a>
                </div>
                <a href="<?=_cfg('href')?>/blog/<?=$v->id?>" class="title"><?=$v->title?></a>
                <div class="info">
                    <div class="dates"><?=date('d M Y', strtotime($v->added))?></div>
                    <a href="<?=_cfg('href')?>/blog/<?=$v->id?>#comments" class="comments hint" attr-msg="<?=t(($v->comments>1?'comments':'comment'))?>"><?=$v->comments?></a>
                    <a href="<?=_cfg('href')?>/blog/<?=$v->id?>" class="views hint" attr-msg="<?=t('views')?>"><?=$v->views?></a>
                    <a href="javascript:void(0);" attr-news-id="<?=$v->id?>" class="like like-icon hint <?=($v->active?'active':null)?>" attr-msg="Like"><?=$v->likes?></a>
                    <div class="clear"></div>
                </div>
            </div>
        <?
            }
        }
        ?>
            <div class="clear"></div>
        </div>
    </div>
</div>