<h1><?=at('links')?></h1>

<a class="back" href="<?=_cfg('cmssite')?>/#links">Back</a>
<table class="links" id="add" name="links">
    <tr>
        <td width="20%"><b><?=at('name')?> <span class="red">*</span></b></td>
        <td><input type="text" id="title" size="50"/></td>
    </tr>
    <tr>
        <td width="20%">
            <b><?=at('link')?></b> 
            <small>http://,  <?=at('or_leave_blank')?>(#)</small>
        </td>
        <td><input type="text" id="href" size="50"/></td>
    </tr>
    <tr>
        <td width="300" class="b"><?=at('made_sublink')?></td>
        <td>
            <select id="main_link" class="chosen">
                <option value="0">(<?=at('no')?>)</option>
                <?
                    foreach($module->links as $v) {
                        ?><option value="<?=$v->id?>"><?=str_replace('web-link-','',$v->value)?></option><?
                    }
                ?>
            </select>
        </td>
    </tr>
    <? if (is_array($module->positions) && $module->positions) { ?>
    <tr>
        <td width="300" class="b"><?=at('link_place')?></td>
        <td>
            <select id="link_block" class="chosen">
                <? foreach($module->positions as $k => $v) { ?>
                    <option value="<?=$k?>"><?=$v?></option>
                <? } ?>
            </select>
        </td>
    </tr>
    <? } ?>
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
    <tr><td colspan="2"><button class="submitButton"><?=at('add_new')?> <?=at('link')?></button></td></tr>
</table>