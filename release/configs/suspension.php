<?
$settings=array();

/* Release Info */
$settings['name']		= 'Suspension';
$settings['header']		= '';

/* Additional Fields */
$settings['fields']		= array(
					array(
						'title'		=> $settings['name'].' position',
						'required'	=> true,
					),
				);

/* Email Stuff */
$settings['business_name']	= $settings['name'].' Studio';
$settings['email_to']		= 'dev@null.com';
$settings['email_from']		= 'dev@null.com';
$settings['email_subject']	= 'Your '.$settings['name'].' release form';
$settings['email_text']		= 'Attached is your release form for your '.strtolower($settings['name']).'.';

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
$settings['age_limit']		= '18';
$settings['parent_header']	= '';

/* Artists Names */
$settings['artists']		= null;
$settings['allow_other_artist']	= false;

/* Provisions */
$settings['provisions']		= array(
					array(
						'title'		=>'Eaten',
						'text'		=>'Have you eaten in the past 4hrs? It\'s a good idea to before hand to increase your blood sugar levels.',
						'required'	=>true,
						'type'		=>'yn',
					),
					array(
						'title'		=>'Physician',
						'text'		=>'Are you currently under the care of a physician, if so what for.',
						'required'	=>true,
						'type'		=>'yn_details',
					),
					array(
						'title'		=>'Medication',
						'text'		=>'Are you currently taking any medications, if so what are they.',
						'required'	=>true,
						'type'		=>'yn_details',
					),
					array(
						'title'		=>'Allergies',
						'text'		=>'Aspirin
Food
Hydrocortisone
Hydroquinone
Skin Bleaching
Latex
Other',
						'required'	=>false,
						'type'		=>'checklist',
					),
					array(
						'title'		=>'Alcohol',
						'text'		=>'How much alcohol did you drink in the last 24 hours?',
						'required'	=>true,
						'type'		=>'text',
					),
					array(
						'title'		=>'History of faiting?',
						'text'		=>'',
						'required'	=>true,
						'type'		=>'yn',
					),
					array(
						'title'		=>'History of bleeding disorders?',
						'text'		=>'',
						'required'	=>true,
						'type'		=>'yn',
					),
					array(
						'title'		=>'Heart Condition or Epilepsy?',
						'text'		=>'',
						'required'	=>true,
						'type'		=>'yn_details',
					),
					array(
						'title'		=>'Latex Allergy?',
						'text'		=>'',
						'required'	=>true,
						'type'		=>'yn',
					),
					array(
						'title'		=>'Hypoglycemia or Diabetes',
						'text'		=>'',
						'required'	=>true,
						'type'		=>'yn',
					),

					array(
						'title'		=>'Photography',
						'text'		=>'I release all rights to any photographs taken of me and the suspension and give consent in advance to their reproduction in print or electronic form.',
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
