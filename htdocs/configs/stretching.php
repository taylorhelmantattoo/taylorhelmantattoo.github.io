<?
$settings=array();

/* Release Info */
$settings['name']		= 'Stretching';
$settings['header']		= '';

/* Additional Fields */
$settings['fields']		= array(
					array(
						'title'		=> $settings['name'].' Location',
						'required'	=> true,
					),
				);

/* Email Stuff */
$settings['business_name']	= 'Piercing Studio';
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
$settings['age_limit']		= '16';
$settings['parent_header']	= '';

/* Artists Names */
$settings['artists']		= false;
$settings['allow_other_artist']	= false;

/* Provisions */
$settings['provisions']		= array(
					array(
						'title'		=>'How did you hear about us?',
						'type'		=>'text',
					),
					array(
						'title'		=>'Eaten',
						'text'		=>'Have you eaten in the past 4hrs? It\'s a good idea to before hand to increase your blood sugar levels.',
						'required'	=>true,
						'type'		=>'yn',
					),
					array(
						'title'		=>'Payment',
						'text'		=>'Do you have cash to pay for your '.strtolower($settings['name']).'? We are unable to accept credit or debit cards',
						'required'	=>true,
						'type'		=>'yn',
					),
					array(
						'title'		=>'Bloodbourne Pathogens',
						'text'		=>'Do you have any bloodbourne pathogens, transmittable diseases or recent illnesses? (It\' okay if you do, we just want to know for our and other\'s safety).',
						'required'	=>true,
						'type'		=>'yn',
					),
					array(
						'title'		=>'Risks',
						'text'		=>'That I have been fully informed of the risks, associated with stretching a piercing. I understand that these risks, known and unknown, can lead to injury, including but not limited to infection, scarring and keloiding and allergic reactions. Having been informed of the potential risks associated with stretching a piercing, I still wish to proceed with the stretching and I freely accept all risks that may arise from stretching.',
						'required'	=>true,
					),
					array(
						'title'		=>'Release',
						'text'		=>'TO WAIVE AND RELEASE to the fullest extent permitted by law each of the Artist and the Piercing Studio from all liability whatsoever, for any and all claims or causes of action that I, my estate, heirs, executors or assigns may have for personal injury or otherwise, including any direct and/or consequential damages, which result or arise, whether caused by the negligence or fault of either the Artist or the Piercing Studio, or otherwise.',
						'required'	=>true,
					),
					array(
						'title'		=>'Permanent change',
						'text'		=>'I acknowledge that the stretching will result in a permanent change to my appearance and that my skin may not be restored to its pre-stretched condition even after its removal.',
						'required'	=>true,
					),
					array(
						'title'		=>'This Document',
						'text'		=>'I acknowledge that I have been given adequate opportunity to read and understand this document, that it was not presented to me at the last minute, and I understand that I am signing a legal contract.',
						'required'	=>true,
					),


					array(
						'title'		=>'Photography',
						'text'		=>'I release all rights to any photographs taken of me and the tattoo and give consent in advance to their reproduction in print or electronic form. (If you do not tick this provision, please advise your Artist).',
						'required'	=>false,
						'type'		=>'checkbox',
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
