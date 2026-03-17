<?php 
$settings=array();

/* Release Info */
$settings['name']               = 'Tattoo';
$settings['header']             = 'Taylor Helman Tattoo — Soul Fire Tattoo Studio, London, Ontario';

/* Additional Fields */
$settings['fields']             = array(
                                        array(
                                                'title'         => $settings['name'].' Placement / Location',
                                                'required'      => true,
                                        ),
                                        array(
                                                'title'         => $settings['name'].' Size (approximate)',
                                                'required'      => false,
                                        ),
                                );

/* Email Stuff */
$settings['business_name']      = 'Taylor Helman Tattoo';
$settings['email_to']           = 'taylorhelmantattoo@gmail.com';
$settings['email_from']         = 'taylorhelmantattoo@gmail.com';
$settings['email_subject']      = 'Signed Release Form — '.$settings['name'];
$settings['email_text']         = 'A signed release form has been submitted for an upcoming tattoo appointment at Taylor Helman Tattoo.

Please find the completed form attached as a PDF for your records.

taylorhelmantattoo.com';

/* Passcode */
$settings['enable_passcode']    = false;
$settings['passcode']           = '0000';

/* Features */
$settings['enable_camera']      = true;
$settings['photo_required']     = false;

/* Newsletter */
$settings['enable_newsletter']  = false;
$settings['newsletter_checked'] = false;

/* Age of consent */
$settings['age_limit']          = '18';
$settings['parent_header']      = '';

/* Artists Names */
$settings['artists']            = array(
                                        'Taylor Helman',
                                );
$settings['allow_other_artist'] = false;
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
						'text'		=>'Do you have your payment ready for your tattoo?',
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
						'text'		=>'That I have been fully informed of the inherent risks, associated with getting a tattoo. I fully understand that these risks, known and unknown, can lead to injury, including but not limited to infection, scarring, difficulties in detecting melanoma and allergic reactions to tattoo pigment, latex gloves, and/or soap. Having been informed of the potential risks, I still wish to proceed with the tattoo application and I freely accept and expressly assume any and all risks.',
						'required'	=>true,
						'type'		=>'checkbox',
					),
					array(
						'title'		=>'Waive',
						'text'		=>'TO WAIVE AND RELEASE to the fullest extent permitted by law each of the Artist and the Studio from all liability whatsoever, for any and all claims or causes of action that I, my estate, heirs, executors or assigns may have for personal injury or otherwise, including any direct and/or consequential damages, which result or arise from my tattoo, whether caused by the negligence or fault of either the Artist or the Tattoo Studio, or otherwise.',
						'required'	=>true,
						'type'		=>'checkbox',
					),
					array(
						'title'		=>'Healing',
						'text'		=>'The Artist and the Tattoo Studio have given me instructions on the care of my tattoo while it\'s healing, and I understand them and will follow them. I acknowledge that it is possible that the tattoo can become infected, particularly if I do not follow the instructions given to me. If any touch-up work to the tattoo is needed due to my own negligence, I agree that the work will be done at my own expense.',
						'required'	=>true,
						'type'		=>'checkbox',
					),
					array(
						'title'		=>'Influence',
						'text'		=>'I am not under the influence of alcohol or drugs, and I am voluntarily submitting to be tattooed by the Artist without duress or coercion.',
						'required'	=>true,
						'type'		=>'checkbox',
					),
					array(
						'title'		=>'Health',
						'text'		=>'I do not have diabetes, epilepsy, hemophilia, a heart condition, nor do I take blood thinning medication. I do not have any other condition that may interfere with the application or healing of the tattoo. I am not the recipient of an organ or bone marrow transplant or, if I am, I have taken the preventive anti-biotics. I am not pregnant or nursing. I do not have a mental impairment that may affect my judgment in getting the tattoo.',
						'required'	=>true,
						'type'		=>'checkbox',
					),
					array(
						'title'		=>'Spelling',
						'text'		=>'Neither the Artist nor the Tattoo Studio is responsible for the meaning or spelling of the symbol or text that I have provided to them or chosen from the flash (design) sheets.',
						'required'	=>true,
						'type'		=>'checkbox',
					),
					array(
						'title'		=>'Fading',
						'text'		=>'Variations in colour/design may exist between the art I have selected and the actual tattoo. I also understand that over time, the colors and the clarity of my tattoo will fade due to natural dispersion of pigment under the skin.',
						'required'	=>true,
						'type'		=>'checkbox',
					),
					array(
						'title'		=>'Permanent',
						'text'		=>'A tattoo is a permanent change to my appearance and can only be removed by laser or surgical means, which can be disfiguring and/or costly and which in all likelihood will not result in the restoration of my skin.',
						'required'	=>true,
						'type'		=>'checkbox',
					),
					array(
						'title'		=>'Legal Action',
						'text'		=>'I agree to reimburse each of the Artist and the Tattoo Studio for any attorneys\' fees and costs incurred in any legal action I bring against either the Artist or the Tattoo Studio and in which either the Artist or the Tattoo Studio is the prevailing party. I agree that the that the courts of New South Whales in Australia shall have personal jurisdiction and venue over me and shall have exclusive jurisdiction for the purpose of litigating any dispute arising out of or related to this agreement.',
						'required'	=>true,
						'type'		=>'checkbox',
					),
					array(
						'title'		=>'Questions',
						'text'		=>'I acknowledge that I have been given adequate opportunity to read and understand this document, that any and all of my questions have been answered, that it was not presented to me at the last minute, and I understand that I am signing a legal contract waiving certain rights to recover against the Artist and the Tattoo Studio.',
						'required'	=>true,
						'type'		=>'checkbox',
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
