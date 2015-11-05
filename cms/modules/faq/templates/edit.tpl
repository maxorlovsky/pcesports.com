<h1><?=$module->moduleName?></h1>

<a class="back" href="<?=_cfg('cmssite')?>/#faq">Back</a>
<table class="table faq" id="edit" name="faq">
    <?
    foreach($module->languages as $v) {
        $question = 'question_';
		$question .= $v->title;
        ?>
        <tr>
            <td width="20%" class="b">Question<span class="red">*</span> - <img src="<?=_cfg('cmsimg').'/flags/'.$v->flag.'.png'?>"/> </td>
            <td><input type="text" id="question_<?=$v->title?>" size="50" value="<?=$module->editData->$question?>" /></td>
        </tr>
        <?
    }
    foreach($module->languages as $v) {
        $answer = 'answer_';
		$answer .= $v->title;
        ?>
        <tr>
            <td width="20%" class="b">Answer<span class="red">*</span> - <img src="<?=_cfg('cmsimg').'/flags/'.$v->flag.'.png'?>"/> </td>
            <td><textarea id="answer_<?=$v->title?>"><?=$module->editData->$answer?></textarea></td>
        </tr>
        <?
    }
    ?>
    <tr>
        <td width="20%" class="b">Order</td>
        <td>
            <select id="order" class="chosen" style="min-width: 100px">
                <? for($i=-100;$i<=100;++$i) { ?>
                    <option value="<?=$i?>" <?=($i==$module->editData->order?'selected="selected"':null)?>><?=$i?></option>
                <? } ?>
            </select>
        </td>
    </tr>
    <tr><td colspan="2"><button class="submitButton"><?=at('change')?> Q&A</button></td></tr>
    <input type="hidden" id="id" value="<?=$module->editData->id?>"/>
</table>