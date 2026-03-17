<?
$settings=array();

/* Release Info */
$settings['name']		= 'Photography';
$settings['header']		= '';

/* Additional Fields */
$settings['fields']		= array(
					array(
						'title'		=> $settings['name'].' Location',
						'required'	=> true,
					),
				);

/* Email Stuff */
$settings['business_name']	= 'Studio';
$settings['email_to']		= 'dev@null.com';
$settings['email_from']		= 'dev@null.com';
$settings['email_subject']	= 'Your '.$settings['name'].' release form';
$settings['email_text']		= 'Attached is your release form for your '.strtolower($settings['name']).'.
	
Brought to you by: StabPad.com';

/* Passcode */
$settings['enable_passcode']	= false;
$settings['passcode']		= '0123';	//4 numbers between 0-5

/* Features */
$settings['enable_camera']	= true;
$settings['photo_required']	= true;

/* Newsletter */
$settings['enable_newsletter']	= true;
$settings['newsletter_checked']	= true;

/* Age of concent */
$settings['age_limit']		= '';
$settings['parent_header']	= '';

/* Artists Names */
$settings['artists']		= false;
$settings['allow_other_artist']	= false;

/* Provisions */
$settings['provisions']		= array(
					array(
						'title'		=>'Photography',
						'text'		=>'I release all rights to any photographs taken of me and the procedure done and give consent in advance to their reproduction in print or electronic form.',
						'required'	=>true,
						'type'		=>'checkbox',
					),
					array(
						'title'		=>'This Document',
						'text'		=>'I acknowledge that I have been given adequate opportunity to read and understand this document, that it was not presented to me at the last minute, and I understand that I am signing a legal contract.',
						'required'	=>true,
					),
				);

/* Provisions Foot */
$settings['provisions_foot']	= '';

/* Required User Fields */
$settings['require_name']	= true;
$settings['require_address']	= false;
$settings['require_email']	= true;
$settings['require_phone']	= false;

?>
