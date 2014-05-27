<section class="container page contacts">

<div class="left-containers">
    <div class="block">
        <div class="block-header-wrapper">
            <h1 class="bordered"><?=t('contact_form')?></h1>
        </div>

        <form class="block-content contact-form">
        	<div id="error"><p></p></div>
        	
        	<div class="fields">
        		<label for="name"><?=t('name')?></label>
        		<input name="name" id="name" type="text" placeholder="<?=t('name')?>*" />
        	</div>
        	<div class="fields">
        		<label for="email">Email</label>
        		<input name="email" id="email" type="text" placeholder="Email*" />
        	</div>
        	<div class="fields">
        		<label for="subject"><?=t('subject')?></label>
        		<select name="subject" id="subject">
        			<option value="<?=t('other')?>"><?=t('other')?></option>
        			<option value="<?=t('question')?>"><?=t('question')?></option>
        			<option value="<?=t('web_broadcast_suggestion')?>"><?=t('web_broadcast_suggestion')?></option>
        			<option value="<?=t('bussiness_offer')?>"><?=t('bussiness_offer')?></option>
        			<option value="<?=t('advertising')?>"><?=t('advertising')?></option>
        		</select>
        	</div>
        	<div class="fields">
        		<label for="msg"><?=t('message')?></label>
        		<textarea name="msg" id="msg" placeholder="<?=t('message_placeholder')?>"></textarea>
        	</div>
        	
        	<a href="javascript:void(0);" class="button" id="submitContactForm"><?=t('send_form')?></a>
        </form>
        
        <div class="success-sent"><p></p></div>
    </div>
</div>