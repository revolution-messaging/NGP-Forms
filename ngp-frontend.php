<?php

/**
 * Shows form used to submit story
 */
function ngp_show_form( $atts, $form=true ) {
	global $wpdb, $ngp;
	
	$api_key = get_option('ngp_api_key', '');
	
	if(empty($api_key)) {
		return 'Not currently configured.';
		exit();
	}
	
	$ngp_error = false;
	
	extract( shortcode_atts( array(
			'id' => null,
			'slug' => null
	), $atts ) );
	
	// To be pulled from DB later.
	// $redirect_url = $res[0]->redirect_url;
	$redirect_url = '/thank-you-for-your-contribution';
	
	$fieldsets = array(
		'Contributor' => array(
			array(
				'type' => 'text',
				'slug' => 'FullName',
				'required' => 'true',
				'label' => 'Name'
			),
			array(
				'type' => 'text',
				'slug' => 'Email',
				'required' => 'false',
				'label' => 'Email Address'
			),
			array(
				'type' => 'text',
				'slug' => 'Address1',
				'required' => 'true',
				'label' => 'Street Address'
			),
			// array(
			// 	'type' => 'text',
			// 	'slug' => 'Address2',
			// 	'required' => 'false',
			// 	'label' => 'Address (Cont.)'
			// 	'show_label' => 'false'
			// ),
			// array(
			// 	'type' => 'text',
			// 	'slug' => 'City',
			// 	'required' => 'true',
			// 	'label' => 'City'
			// ),
			// array(
			// 	'type' => 'text',
			// 	'slug' => 'State',
			// 	'required' => 'true',
			// 	'label' => 'State'
			// 	'options' => array('AK'=>'AK','AL'=>'AL','AR'=>'AR','AZ'=>'AZ','CA'=>'CA','CO'=>'CO','CT'=>'CT','DC'=>'DC','DE'=>'DE','FL'=>'FL','GA'=>'GA','HI'=>'HI','IA'=>'IA','ID'=>'ID','IL'=>'IL','IN'=>'IN','KS'=>'KS','KY'=>'KY','LA'=>'LA','MA'=>'MA','MD'=>'MD','ME'=>'ME','MI'=>'MI','MN'=>'MN','MO'=>'MO','MS'=>'MS','MT'=>'MT','NC'=>'NC','ND'=>'ND','NE'=>'NE','NH'=>'NH','NJ'=>'NJ','NM'=>'NM','NV'=>'NV','NY'=>'NY','OH'=>'OH','OK'=>'OK','OR'=>'OR','PA'=>'PA','RI'=>'RI','SC'=>'SC','SD'=>'SD','TN'=>'TN','TX'=>'TX','UT'=>'UT','VA'=>'VA','VT'=>'VT','WA'=>'WA','WI'=>'WI','WV'=>'WV','WY'=>'WY')
			// ),
			array(
				'type' => 'text',
				'slug' => 'Zip',
				'required' => 'true',
				'label' => 'Zip Code'
			)
		),
		'Employment' => array(
			'html_intro' => '<p>Federal law requires us to use our best efforts to collect and report the name, mailing address, occupation, and employer of individuals whose contributions exceed $200 in an election cycle.</p>',
			array(
				'type' => 'text',
				'slug' => 'Employer',
				'required' => 'true',
				'label' => 'Employer'
			),
			array(
				'type' => 'text',
				'slug' => 'Occupation',
				'required' => 'true',
				'label' => 'Occupation (if none, put "n/a" or "self-employed")'
			)
		),
		'Credit card' => array(
			'html_intro' => '<p id="accepted-cards" style="margin: 0pt 0pt -5px; background: url(/wp-content/plugins/'.plugin_basename(dirname(__FILE__)).'/credit-card-logos.png) no-repeat scroll 0% 0% transparent; text-indent: -900em; width: 211px; height: 34px;">We accept Visa, Mastercard, American Express and Discover cards.</p>',
			array(
				'type' => 'radio',
				'slug' => 'Amount',
				'required' => 'true',
				'label' => 'Amount',
				'options' => array(
					'10.00' => '$10',
					'25.00' => '$25',
					'50.00' => '$50',
					'100.00' => '$100',
					'250.00' => '$250',
					'500.00' => '$500',
					'1000.00' => '$1,000',
					'custom' => '<label for="ngp_custom_dollar_amt">Other:</label> <input type="text" name="custom_dollar_amt" class="ngp_custom_dollar_amt" /> (USD)'
				)
			),
			array(
				'type' => 'text',
				'slug' => 'CreditCardNumber',
				'required' => 'true',
				'label' => 'Credit Card Number'
			),
			array(
				'type' => 'text',
				'slug' => 'ExpMonth',
				'required' => 'true',
				'label' => 'Expiration Month/Year (MM/YY)',
				'show_label' => 'true',
				'show_placeholder' => 'false',
				'show_post_div' => 'false'
			),
			array(
				'type' => 'text',
				'slug' => 'ExpYear',
				'required' => 'true',
				'label' => 'Expiration Year',
				'show_label' => 'false',
				'show_placeholder' => 'false',
				'show_pre_div' => 'false'
			),
		)
		// array(
		// 	'type' => 'checkbox',
		// 	'slug' => 'RecurringContrib',
		// 	'required' => 'true',
		// 	'label' => 'Recurring Contribution?'
		// 	'show_label' => 'false'
		// 	'show_placeholder' => 'false'
		// )
	);
	
	// Get the Form from the DB
	// TODO: Later, let's make the forms configurable
	// if($id) {
	// 	$res = $wpdb->get_results('SELECT * FROM '.$psc->forms.' WHERE id="'.$id.'"');
	// } else if($slug) {
	// 	$res = $wpdb->get_results('SELECT * FROM '.$psc->forms.' WHERE slug="'.mysql_escape_string($slug).'" LIMIT 0, 1');
	// } else {
	// 	echo '<p style="background:pink;border:1px solid red;color:red;padding:5px 10px;">Error, you must provide a slug or id to "ngp_show_forms".</p>';
	// }
	// 
	// $fields = unserialize($res[0]->data);
	
	$resp = (object) array('is_valid' => true);
	
	// if(count($res) == 1) {
	$any_errors = false;
	if(wp_verify_nonce($_POST['ngp_add'], 'ngp_nonce_field') && $_POST['ngp_form_id']==$id)
	{
		foreach($fieldsets as $fieldset) {
			foreach($fieldset as $key => $field) {
				if($key!='html_intro') {
					if($field['required']=='true' && (!isset($_POST[$field['slug']]) || empty($_POST[$field['slug']]))) {
						$fields[$key]['error'] = true;
						$any_errors = true;
					}
				}
			}
		}
		
		if(!$resp->is_valid) {
			$any_errors = true;
		}
			
		if(!$any_errors) {
			// Split Name
			$payment_data = $_POST;
			if(isset($_POST['FullName']) && !empty($_POST['FullName'])) {
				$names = explode(' ', $_POST['FullName']);
				// Attempt payment
				unset($payment_data['ngp_form_id']);
				unset($payment_data['ngp_add']);
				unset($payment_data['FullName']);
				unset($payment_data['_wp_http_referer']);
				$payment_data['FirstName'] = $names[0];
				$payment_data['LastName'] = $names[(count($names)-1)];
				
				// setlocale(LC_MONETARY, 'en_US');
				if(!empty($payment_data['custom_dollar_amt'])) {
					// $payment_data['Amount'] = str_replace('$', '', money_format('%.2n', $payment_data['custom_dollar_amt']));
					$payment_data['Amount'] = number_format($payment_data['custom_dollar_amt'], 2, '.', '');
				} else {
					// $payment_data['Amount'] = str_replace('$', '', money_format('%.2n', $payment_data['Amount']));
					$payment_data['Amount'] = number_format($payment_data['Amount'], 2, '.', '');
				}
				unset($payment_data['custom_dollar_amt']);
				$payment_data['Cycle'] = date('Y');
				
				require_once('NgpDonation.php');
				$send_email  = (isset($payment_data['Email']) && !empty($payment_data['Email'])) ? true : false;
				$donation = new NgpDonation($api_key, $send_email, $payment_data);
				if($donation->save()) {
					// Success!
					// Redirect.
					wp_redirect($redirect_url);
					exit;
				} else {
					// Failure.
					$ngp_error = true;
				}
			} else {
				$field['Contributor'][0]['error'] = true;
			}
			
		}
	} else if(!empty($_POST) && isset($_POST['ngp_add']) && !wp_verify_nonce($_POST['ngp_add'], 'ngp_nonce_field')) {
		$ngp_error = true;
	}
	/* else if(!empty($_POST) && $_POST['ngp_form_id']!=$id) {
		$ngp_error = true;
	} */
		
	$form_fields = '';
	// Loop through and generate the elements
		
	foreach($fieldsets as $fieldset_name => $fields) {
		$form_fields .= '<fieldset><legend>'.$fieldset_name.'</legend>';
		if(isset($fields['html_intro'])) {
			$form_fields .= $fields['html_intro'];
			unset($fields['html_intro']);
		}
		foreach($fields as $field_key => $field) {
			switch($field['type']) {
				case 'text':
					if(!isset($field['show_pre_div']) || $field['show_pre_div']=='true') {
						$form_fields .= '
							<div class="input';
						if(isset($field['error']) && $field['error']===true) {
							$form_fields .= ' error';
						}
						$form_fields .= '">';
					}
					if(isset($field['error']) && $field['error']===true) {
						$form_fields .= '<div class="errMsg">This field cannot be left blank.</div>';
					}
					if(!isset($field['show_label']) || $field['show_label']!='false') {
						$form_fields .= '
								<label for="'.$field['slug'].'">'.$field['label'];
						if($field['required']=='true') { $form_fields .= ' <span class="required">*</span>'; }
						$form_fields .= '</label>';
					}
					$form_fields .= '<input type="text" name="'.$field['slug'].'" id="'.$field['slug'].'" value="';
					if(isset($_POST[$field['slug']])) {
						$form_fields .= $_POST[$field['slug']];
					}
					$form_fields .= '"';
					if(!empty($field['label']) && (!isset($field['show_placeholder']) || $field['show_placeholder']=='true')) {
						$form_fields .= ' placeholder="'.$field['label'].'"';
					}
					$form_fields .= ' />';
					if(!isset($field['show_post_div']) || $field['show_post_div']=='true') {
						$form_fields .= '</div>';
					}
					break;
				case 'file':
					$file = true;
					$form_fields .= '
						<div class="file';
						if(isset($field['error']) && $field['error']===true) {
							$form_fields .= ' error';
						}
						$form_fields .= '">';
						if(isset($field['error']) && $field['error']===true && $field['required']=='true') {
							$form_fields .= '<div class="errMsg">You must provide a '.$field['label'].'.</div>';
						} else if(isset($field['error']) && $field['error']===true) {
							$form_fields .= '<div class="errMsg">There was a problem uploading your file.</div>';
						}
						
						$form_fields .= '
								<label for="'.$field['slug'].'">'.$field['label'];
						if($field['required']=='true') { $form_fields .= ' <span class="required">*</span>'; }
						$form_fields .= '</label>
							<input type="file" name="'.$field['slug'].'" id="'.$field['slug'].'" />
						</div>
					';
					break;
				case 'hidden':
					$form_fields .= '<input type="hidden" name="'.$field['slug'].'" id="'.$field['slug'].'" value="';
					if(isset($_POST[$field['slug']])) {
						$form_fields .= $_POST[$field['slug']];
					} else if(isset($field['value'])) {
						$form_fields .= $field['value'];
					}
					$form_fields .= '" />';
					break;
				case 'password':
					$form_fields .= '
					<div class="password	';
						if(isset($field['error']) && $field['error']===true) {
							$form_fields .= ' error';
						}
						$form_fields .= '">';
						if(isset($field['error']) && $field['error']===true) {
							$form_fields .= '<div class="errMsg">This field cannot be left blank.</div>';
						}
						$form_fields .= '
								<label for="'.$field['slug'].'">'.$field['label'];
						if($field['required']=='true') { $form_fields .= ' <span class="required">*</span>'; }
						$form_fields .= '</label>
					<input type="password" name="'.$field['slug'].'" id="'.$field['slug'].'" value="';
					if(isset($_POST[$field['slug']])) {
						$form_fields .= $_POST[$field['slug']];
					}
					$form_fields .= '"/>
					</div>
					';
					break;
				case 'textarea':
					$form_fields .= '
					<div class="textarea';
					if(isset($field['error']) && $field['error']===true) {
						$form_fields .= ' error';
					}
					$form_fields .= '">';
					if(isset($field['error']) && $field['error']===true) {
						$form_fields .= '<div class="errMsg">This field cannot be left blank.</div>';
					}
					$form_fields .= '
							<label for="'.$field['slug'].'">'.$field['label'];
					if($field['required']=='true') { $form_fields .= ' <span class="required">*</span>'; }
					$form_fields .= '</label>
					<textarea name="'.$field['slug'].'" id="'.$field['slug'].'">';
					if(isset($_POST[$field['slug']])) {
						$form_fields .= $_POST[$field['slug']];
					}
					$form_fields .= '</textarea>
					</div>
					';
					break;
				case 'checkbox':
					if(isset($field['options']) && !empty($field['options'])) {
						$form_fields .= '<fieldset id="ngp_'.$field['slug'].'" class="checkboxgroup';
						if(isset($field['error']) && $field['error']===true) {
							$form_fields .= ' error">
							<div class="errMsg">You must check at least one.</div>';
						} else {
							$form_fields .= '">';
						}
						$form_fields .= '<legend>'.$field['label'];
						if($field['required']=='true') $form_fields .= '<span class="required">*</span>';
						$form_fields .= '</legend>';
						$i = 0;
						foreach($field['options'] as $val) {
							$i++;
							$form_fields .= '<div class="checkboxoption"><input type="checkbox" value="'.$val.'" name="'.$field['slug'].'['.$i.']['.$val.']" id="option_'.$i.'_'.$field['slug'].'" class="'.$field['slug'].'" /> <label for="option_'.$i.'_'.$field['slug'].'">'.$val.'</label></div>'."\r\n";
						}
						$form_fields .= '</fieldset>';
					} else {
						$form_fields .= '<div id="ngp_'.$field['slug'].'" class="checkbox">';
						$form_fields .= '<div class="checkboxoption"><input type="checkbox" name="'.$field['slug'].'" id="'.$field['slug'].'" class="'.$field['slug'].'" /> <label for="'.$field['slug'].'">'.$field['label'].'</label></div>'."\r\n";
						$form_fields .= '</div>';
					}
					break;
				case 'radio':
					$form_fields .= '
					<fieldset id="ngp_'.$field['slug'].'" class="radiogroup';
					if(isset($field['error']) && $field['error']===true) {
						$form_fields .= ' error';
					}
					$form_fields .= '"><legend>'.$field['label'];
					if($field['required']=='true') { $form_fields .= '<span class="required">*</span>'; }
					$form_fields .= '</legend>';
					if(isset($field['error']) && $field['error']===true) {
						$form_fields .= '<div class="errMsg">You must select an option.</div>';
					}
					$i = 0;
					foreach($field['options'] as $val => $labe) {
						$i++;
						if($val=='custom') {
							$form_fields .= '<div class="radio custom-donation-amt">'.$labe.'</div>'."\r\n";
						} else {
							$form_fields .= '<div class="radio"><input type="radio" value="'.$val.'" name="'.$field['slug'].'" id="'.$i.'_'.$field['slug'].'" class="'.$field['slug'].'"> <label for="'.$i.'_'.$field['slug'].'">'.$labe.'</label></div>'."\r\n";
						}
					}
					$form_fields .= '</fieldset>';
					break;
				case 'select':
					$form_fields .= '
					<div class="select';
						if(isset($field['error']) && $field['error']===true) {
							$form_fields .= ' error';
						}
						$form_fields .= '">';
						if(isset($field['error']) && $field['error']===true) {
							$form_fields .= '<div class="errMsg">You must select an option.</div>';
						}
						$form_fields .= '
								<label for="'.$field['slug'].'">'.$field['label'];
						if($field['required']=='true') { $form_fields .= ' <span class="required">*</span>'; }
						$form_fields .= '</label>
						<select name="'.$field['slug'].'" id="'.$field['slug'].'">'."\r\n";
						if($field['slug']!='State') {
							$form_fields .= '
							<option>Select an option...</option>
							';
						}
						foreach($field['options'] as $key => $val) {
							$form_fields .= '<option value="'.$key.'"';
							if(isset($default_state) && $default_state==$key) {
								$form_fields .= ' selected="selected"';
							}
							$form_fields .= '>'.$val.'</option>'."\r\n";
						}
						$form_fields .= '
						</select>
					</div>
					';
					break;
				case 'multiselect':
					$form_fields .= '
					<div class="multiselect	';
						if(isset($field['error']) && $field['error']===true) {
							$form_fields .= ' error';
						}
						$form_fields .= '">';
						if(isset($field['error']) && $field['error']===true) {
							$form_fields .= '<div class="errMsg">This field cannot be left blank.</div>';
						}
						$form_fields .= '
								<label for="'.$field['slug'].'">'.$field['label'];
						if($field['required']=='true') { $form_fields .= ' <span class="required">*</span>'; }
						$form_fields .= '</label>
						<select multiple name="'.$field['slug'].'" id="'.$field['slug'].'">'."\r\n";
							foreach($field['options'] as $key => $val) {
								$form_fields .= '<option value="'.$key.'">'.$val.'</option>'."\r\n";
							}
							$form_fields .= '
						</select>
					</div>
					';
					break;
			}
		}
		$form_fields .= '</fieldset>';
	}
	if($ngp_error) { echo '<div id="ngp_alert">Sorry, but your payment could not be processed. Please try again or call '.get_option('ngp_support_phone').'</div>'; }
	
	if(!empty($form_fields)) {
	?>
	<form <?php if($file) { echo 'enctype="multipart/form-data" '; } ?>name="ngp_user_news" class="ngp_user_submission" id="ngp_form" action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">
		<?php
		echo '<input type="hidden" name="ngp_form_id" id="ngp_form_id" value="'.$id.'" />';
			
		if(function_exists('wp_nonce_field')) {
			wp_nonce_field('ngp_nonce_field', 'ngp_add');
		}
			
		echo $form_fields;
			
		?>
		<div class="submit">
			<input type="submit" value="Donate Now" />
		</div>
		<p class="ngp-small-print">By clicking on the "Donate now" button above you confirm that the following statements are true and accurate:</small>
		<ol class="ngp-small-print">
			<li>I am a United States citizen or a lawfully admitted permanent resident of the United States.</li>
			<li>This contribution is not made from the general treasury funds of a corporation, labor organization or national bank.</li>
			<li>This contribution is not made from the treasury of an entity or person who is a federal contractor.</li>
			<li>This contribution is not made from the funds of a political action committee.</li>
			<li>This contribution is not made from the funds of an individual registered as a federal lobbyist or a foreign agent, or an entity that is a federally registered lobbying firm or foreign agent.</li>
			<li>I am not a minor under the age of 16.</li>
			<li>The funds I am donating are not being provided to me by another person or entity for the purpose of making this contribution.</li>
		</ol>
		
		<?php echo get_option('ngp_footer_info'); ?>
	</form>
	<?php
	}
	// } else if(count($res)==0) {
	// 	echo 'Sorry, but we couldn\'t find a form with ';
	// 	if($slug) { echo 'slug: '.$slug; }
	// 	else if($id) { echo 'id: '.$id; }
	// 	echo '.';
	// } else {
	// 	echo 'Sorry, but we got more than 1 form back.';
	// }
	// if($ngp_error)
	// 	return false;
	// else if(isset($ngp_success))
	// 	return 'moderation';
	// else
	// 	return true;
}