<?php defined('AUTOMAD') or die('Direct access not permitted!'); ?>	
	<@ standard/mail {
		to: @{ email },
		success: '<div class=\"uk-margin-bottom uk-text-bold\">
					@{ notificationMailSuccess 
						| def ("Successfully sent email!") 
					}
				  </div>',
		error: '<div class=\"uk-margin-bottom uk-text-bold\">
					@{ notificationMailError 
						| def ("Please fill out all fields!") 
					}
				</div>'
	} @>
	<form class="uk-form" action="@{ url }" method="post">
		<input type="text" name="human" value="" />		
		<div class="uk-form-row">
			<input 
			class="uk-form-controls uk-width-1-1" 
			type="text" 
			name="from" 
			value="" 
			placeholder="@{ placeholderEmail | def ('Your Email Address')}" 
			/>
		</div>
		<div class="uk-form-row">
			<input 
			class="uk-form-controls uk-width-1-1" 
			type="text" 
			name="subject" 
			value="" 
			placeholder="@{ placeholderSubject | def ('Subject') }" 
			/>
		</div>
		<div class="uk-form-row">
			<textarea 
			class="uk-form-controls uk-width-1-1" 
			name="message" 
			rows="5" 
			placeholder="@{ placeholderMessage | def ('Message') }"
			></textarea>
		</div>
		<button class="uk-button uk-margin-large-top" type="submit">
			<i class="uk-icon-paper-plane"></i>&nbsp;
			@{ labelSendMail | def ('Send Mail') }
		</button>
	</form>