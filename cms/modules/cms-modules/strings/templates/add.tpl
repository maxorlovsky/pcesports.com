<h1><?=at('strings')?></h1>

<a class="back" href="<?=_cfg('cmssite')?>/#strings">Back</a>
<table class="table strings" id="add" name="strings">
    <tr>
        <td width="20%"><b><?=at('name')?> <span class="red">*</span></b></td>
        <td><input type="text" id="title" size="50"/></td>
    </tr>
    <?
    foreach($module->languages as $v) {
        ?>
        <tr>
            <td class="b"><?=at('text')?> - <img src="<?=_cfg('cmsimg').'/flags/'.$v->flag.'.png'?>"/></td>
            <td><textarea id="string_<?=$v->title?>" cols="80"></textarea></td>
        </tr>
        <?
    }
    ?>
    <tr><td colspan="2"><button class="submitButton"><?=at('add_new')?> <?=at('string')?></button></td></tr>
</table>