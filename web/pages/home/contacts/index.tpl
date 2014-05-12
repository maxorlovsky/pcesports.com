<section class="container page contacts">

<div class="left-containers">
    <div class="block">
        <div class="block-header-wrapper">
            <h1 class="bordered">Contact form</h1>
        </div>

        <form class="block-content contact-form">
        	<div id="error"><p></p></div>
        	
        	<div class="fields">
        		<label for="name">Name</label>
        		<input name="name" id="name" type="text" placeholder="Name*" />
        	</div>
        	<div class="fields">
        		<label for="email">Email</label>
        		<input name="email" id="email" type="text" placeholder="Email*" />
        	</div>
        	<div class="fields">
        		<label for="subject">Subject</label>
        		<select name="subject" id="subject">
        			<option value="Other">Other</option>
        			<option value="Question">Question</option>
        			<option value="Web/broadcast suggestion">Web/broadcast suggestion</option>
        			<option value="Bussiness offer">Bussiness offer</option>
        			<option value="Advertising">Advertising</option>
        		</select>
        	</div>
        	<div class="fields">
        		<label for="msg">Message</label>
        		<textarea name="msg" id="msg" placeholder="Message if needed"></textarea>
        	</div>
        	
        	<a href="javascript:void(0);" class="button" id="submitContactForm">Send form</a>
        </form>
        
        <div class="block-content success-sent"><p></p></div>
    </div>
</div>