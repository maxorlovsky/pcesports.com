<script>
	var requireStatus = 0;
	<? if (isset($_SESSION['participant']) && $_SESSION['participant']->id) { ?>
	var requireStatus = 1;
	<? } ?>
</script>
	
<div class="right-containers">
    <div class="block boards">
        <? include_once _cfg('pages').'/boards/snippet.tpl'; ?>
    </div>
</div>

<div class="clear"></div>