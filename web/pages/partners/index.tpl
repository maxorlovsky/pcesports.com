<section class="container page team">

<div class="left-containers">
    <div class="block">
        <div class="block-header-wrapper">
            <h1 class="bordered"><?=t('partners')?></h1>
        </div>

        <div class="block-content partners">
        	<?
        	if ($this->partners) {
        		foreach($this->partners as $k => $v) {
			?>
    			<div class="partner <?=$k?>">
    				<div class="logo"><img src="<?=$v['logo']?>" alt="<?=$k?>" /></div>
    				<div class="text"><?=$v['text']?></div>
    				<a class="button link" href="<?=$v['link']?>" target="_blank" class="link"><?=t('visit_website')?></a>
    			</div>
			<?
        		}
        	}
        	?>
        </div>
    </div>
</div>