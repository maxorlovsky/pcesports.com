<h1>News</h1>

<a class="back" href="<?=_cfg('cmssite')?>/#news">Back</a>
<table class="table news" id="add" name="news">
    <tr>
        <td width="20%"><b>Title <span class="red">*</span></b></td>
        <td><input type="text" id="title" size="50"/></td>
    </tr>
    <tr>
        <td width="20%"><b>Image</b></td>
        <td>
            <button id="upload">Add image</button>
            <div id="uploadStatus"></div>
            <div id="file"></div>
            <input type="hidden" id="uploadedFiles" />
        </td>
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
    <tr><td colspan="2"><button class="submitButton"><?=at('add_new')?> article</button></td></tr>
</table>

<script>
new AjaxUpload($('#upload'), {  
    action: site,
    data: {
    	control: 'submitForm',
        module: 'news',
        action: 'uploadImage'
    },
    name: 'upload',  
    onSubmit: function(file, ext){  
        if (! (ext && /^(jpg|png|jpeg)$/.test(ext))) {  
            $('#uploadStatus').html('Only JPG, PNG files are allowed');  
            return false; 
        }
        $('#uploadStatus').html('Uploading...');  
    },  
    onComplete: function(file, response) {
        $('#uploadStatus').html('');  
        
        answer = response.split(';');
        if(answer[0] != 1){
            $('#file').html('Error: '+answer[1]);
            $('#uploadedFiles').val('0');
        } else{
            $('#file').html('<img src="'+answer[1]+'" />');
            $('#uploadedFiles').val(answer[2]);
        }  
    }  
});
</script>