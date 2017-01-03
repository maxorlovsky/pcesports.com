<h1><?=$module->moduleName?></h1>

<a class="back" href="<?=_cfg('cmssite')?>/#faq">Back</a>
<table class="table faq" id="add" name="faq">
    <?
    foreach($module->languages as $v) {
        ?>
        <tr>
            <td width="20%" class="b">Question<span class="red">*</span> - <img src="<?=_cfg('cmsimg').'/flags/'.$v->flag.'.png'?>"/> </td>
            <td><input type="text" id="question_<?=$v->title?>" size="50" value="" /></td>
        </tr>
        <?
    }
    foreach($module->languages as $v) {
        ?>
        <tr>
            <td width="20%" class="b">Answer<span class="red">*</span> - <img src="<?=_cfg('cmsimg').'/flags/'.$v->flag.'.png'?>"/> </td>
            <td><textarea id="answer_<?=$v->title?>"></textarea></td>
        </tr>
        <?
    }
    ?>
    <tr>
        <td width="20%" class="b">Order</td>
        <td>
            <select id="order" class="chosen" style="min-width: 100px">
                <? for($i=-100;$i<=100;++$i) { ?>
                    <option value="<?=$i?>" <?=($i==0?'selected="selected"':null)?>><?=$i?></option>
                <? } ?>
            </select>
        </td>
    </tr>
    <tr><td colspan="2"><button class="submitButton"><?=at('add_new')?> Q&A</button></td></tr>
</table>