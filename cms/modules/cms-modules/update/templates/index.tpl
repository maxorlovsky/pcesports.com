<h1><?=at('update')?></h1>

<div class="update box">

<div class="">
	<p>Your current version: <b><?=$this->data->cmsSettings['version']?></b></p>
    <p>Available version: <b><?=$module->version?></b></p>
    <? if ($this->data->cmsSettings['version'] != $module->version) { ?>
        <br />
        <p>To update CMS manually, you need to download this archive and upload it to root folder, with changing all asked files.</p>
        <p><a href="#">Download archive</a></p>
        <p>Afterwards you must use <a href="#">this link</a> to update database</p>
    <? } ?>
    <br />
    <p>You can't skip updates, updates will be installed one by one in order of development</p>
</div>

<div class="clear"></div>

</div>

<script>

</script>