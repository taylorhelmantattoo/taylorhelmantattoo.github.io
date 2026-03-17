<?
$settings=array();

/* Release Info */
$settings['name']		= 'Relase Form';
$settings['header']		= '';

/* Shop Info */
$settings['business_name']	= '';
$settings['procedure_name']	= '';		// emtpy string or false to disable
$settings['procedure_default']	= '';

/* Email Stuff */
$settings['email_to']		= '';
$settings['email_from']		= '';
$settings['email_subject']	= 'Your release form';
$settings['email_text']		= '';

/* Passcode */
$settings['enable_passcode']	= false;
$settings['passcode']		= '0000';	//4 numbers between 0-5

/* Features */
$settings['enable_camera']	= true;
$settings['camera_required']	= true;

/* Newsletter */
$settings['enable_newsletter']	= true;
$settings['newsletter_checked']	= true;

/* Age of concent */
$settings['age_limit']		= '';
$settings['parent_header']	= '';

/* Artists Names */
$settings['artists']		= array(
					'',
				);

/* Provisions */
$settings['provisions']		= array(
					array(
						'title'		=>'',
						'text'		=>'',
						'required'	=>true,
					),
				);

/* Provisions Foot */
$settings['provisions_foot']	= '';

/* Required User Fields */
$settings['require_name']	= true;
$settings['require_address']	= false;
$settings['require_email']	= true;

?>
