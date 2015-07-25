<section class="container page tournament">

<div class="left-containers">
    <div class="block">
        <div class="block-header-wrapper">
            <h1 class="bordered"><?=t('edit_information')?> "<?=$this->participant->name?>"</h1>
        </div>
        
        <div class="block-content">
            <p class="team-edit-completed success-add"><?=t('info_edited')?></p>
            <div id="join-form">
                <form id="da-form" method="post">
                    <div class="form-item" data-label="phone">
                        <input type="text" name="phone" placeholder="Phone number" value="<?=$this->participant->contact_info->phone?>" />
                        <div class="message hidden"></div>
                    </div>

                    <? for ($i=1;$i<=3;++$i) { ?>
                    <div class="form-item" data-label="hero<?=$i?>">
                        <select class="hero<?=$i?>" name="hero<?=$i?>">
                            <? foreach($this->heroes as $k => $v) {
                                $hero = hero.$i;
                            ?>
                                <option value="<?=$k?>" <?=($this->participant->contact_info->$hero==$k?'selected':null)?>><?=ucfirst($v)?></option>
                            <? } ?>
                        </select>
                        <div class="message hidden"></div>
                    </div>
                    <? } ?>
                    
                    <div class="heroes-images">
                        <h6><?=t('your_classes')?></h6>
                        <? for ($i=1;$i<=3;++$i) { ?>
                        <div id="hero<?=$i?>img" class="hsicons" attr-picked=""></div>
                        <? } ?>
                    </div>
                    <div class="clear"></div>

                    <input type="hidden" name="participant" value="<?=$_GET['val3']?>" />
                    <input type="hidden" name="link" value="<?=$_GET['val4']?>" />
                </form>
                <div class="clear"></div>
                <a href="javascript:void(0);" class="button" id="edit-in-tournament"><?=t('edit_information')?></a>
            </div>
        </div>
    </div>
</div>