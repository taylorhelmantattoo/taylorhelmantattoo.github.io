<?php 
$settings=array();

/* Release Info */
$settings['name']               = 'Tattoo';
$settings['header']             = 'Taylor Helman Tattoo - Soul Fire Tattoo Studio, London, Ontario';

/* Additional Fields */
$settings['fields']             = array(
                                        array(
                                                'title'         => 'Placement of tattoo',
                                                'required'      => true,
                                        ),
                                );

/* Email Stuff */
$settings['business_name']      = 'Taylor Helman Tattoo';
$settings['email_to']           = 'taylorhelmantattoo@gmail.com';
$settings['email_from']         = 'taylorhelmantattoo@gmail.com';
$settings['email_subject']      = 'Signed Release Form - '.$settings['name'];
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
$settings['provisions']         = array(

                                        array(
                                                'title'         => 'Flu Symptoms',
                                                'text'          => 'Do you have any flu like symptoms?',
                                                'required'      => true,
                                                'type'          => 'yn',
                                        ),
                                        array(
                                                'title'         => 'Eaten',
                                                'text'          => 'Have you eaten in the last 3 hours? It\'s a good idea to eat before hand to increase your blood sugar levels.',
                                                'required'      => true,
                                                'type'          => 'yn',
                                        ),
                                        array(
                                                'title'         => 'Fainting',
                                                'text'          => 'Are you prone to fainting?',
                                                'required'      => true,
                                                'type'          => 'yn',
                                        ),
                                        array(
                                                'title'         => 'First Tattoo',
                                                'text'          => 'Is this your first tattoo?',
                                                'required'      => true,
                                                'type'          => 'yn',
                                        ),
                                        array(
                                                'title'         => 'Pregnant or Nursing',
                                                'text'          => 'Are you pregnant or nursing? If so, you cannot be tattooed at this time. Please let your artist know.',
                                                'required'      => true,
                                                'type'          => 'yn',
                                        ),
                                        array(
                                                'title'         => 'Medical Conditions',
                                                'text'          => 'Do you have any medical conditions we should know about? Please advise your artist if there is anything they should be aware of, or if there is anything they can do to make your session more comfortable.',
                                                'required'      => true,
                                                'type'          => 'yn_details',
                                        ),
                                        array(
                                                'title'         => 'Health',
                                                'text'          => 'I do not suffer from any disease that may cause poor healing of my new tattoo (such as acne, moles, scarring, eczema, psoriasis, or a sunburn). I do not have a heart condition, or take medicine which thins the blood. If I suffer from any of these issues, I have informed my tattoo artist of this fact.',
                                                'required'      => true,
                                                'type'          => 'checkbox',
                                        ),
                                        array(
                                                'title'         => 'Influence',
                                                'text'          => 'I am not under the influence of alcohol or drugs. I acknowledge that obtaining this tattoo is my choice alone and will result in a permanent change to my appearance.',
                                                'required'      => true,
                                                'type'          => 'checkbox',
                                        ),
                                        array(
                                                'title'         => 'Spelling & Symbolism',
                                                'text'          => 'If my tattoo contains symbols, words or numbers in any language I acknowledge that it is my responsibility that they are spelt properly or drawn correctly and mine alone.',
                                                'required'      => true,
                                                'type'          => 'checkbox',
                                        ),
                                        array(
                                                'title'         => 'Permanent',
                                                'text'          => 'A tattoo is a permanent change to my appearance and can only be removed by laser or surgical means, which can be disfiguring and/or costly and which in all likelihood will not result in the restoration of my skin.',
                                                'required'      => true,
                                                'type'          => 'checkbox',
                                        ),
                                        array(
                                                'title'         => 'Fading',
                                                'text'          => 'Variations in colour/design may exist between the art I have selected and the actual tattoo. I also understand that over time, the colors and the clarity of my tattoo will fade due to natural dispersion of pigment under the skin. I understand that if my skin is darker, the colours will not appear as bright as they do on lighter skin tones.',
                                                'required'      => true,
                                                'type'          => 'checkbox',
                                        ),
                                        array(
                                                'title'         => 'Aftercare',
                                                'text'          => 'I understand my artist will give me instructions on the care of my tattoo to follow while it\'s healing, and I will follow them. I acknowledge that it is possible that the tattoo can become infected, particularly if I do not follow the instructions given to me. If any touch-up work to the tattoo is needed due to my own negligence, I agree that the work will be done at my own expense.',
                                                'required'      => true,
                                                'type'          => 'checkbox',
                                        ),
                                        array(
                                                'title'         => 'Touch-ups',
                                                'text'          => 'Each tattoo is entitled to one free touch-up if it is needed. I acknowledge that a touch-up is only complimentary if it is done within the first 6 months of the initial tattoo date. Anything after 6 months of healing requires a touch up fee of $50+tax per touch-up. Feet, hands, and fingers do not qualify for a free touch-up as those areas fade very quickly and that is out of the artist\'s realm of control. If you arrive to your tattoo appointment with numbing cream on, without previously discussing with your artist, you may be charged the $50 touch up fee, as we can no longer guarantee the healing of your tattoo.',
                                                'required'      => true,
                                                'type'          => 'checkbox',
                                        ),
                                        array(
                                                'title'         => 'Liability',
                                                'text'          => 'I agree to release and not hold Soul Fire Tattoo Studio, its owner, or contractors, liable from any claims, damages, or legal action arising in any way from my tattoo procedure and conduct used to perform this tattoo. I agree not to pursue legal action towards anyone at Soul Fire Tattoo Studio, staff or contractors.',
                                                'required'      => true,
                                                'type'          => 'checkbox',
                                        ),
                                        array(
                                                'title'         => 'Photography',
                                                'text'          => 'I release all rights to any photographs taken of me and the tattoo and give consent in advance to their reproduction in print or electronic form. (If you do not tick this provision, please advise your Artist).',
                                                'required'      => false,
                                                'type'          => 'checkbox',
                                        ),
                                        array(
                                                'title'         => 'Waive',
                                                'text'          => 'I acknowledge by signing this agreement that I have been given full opportunity to ask questions I may have about obtaining a tattoo at Soul Fire Tattoo Studio and that all of my questions have been answered to my full satisfaction. I specifically acknowledge by signing this document that I have read and understand the facts set forth above, and agree to all of the points. I CONFIRM UNDER PENALTY THAT I HAVE READ THIS RELEASE FORM AND THAT I UNDERSTAND ALL POINTS AND MY INFORMATION IS TRUE AND I AGREE TO LEGALLY BE BOUND BY IT. If any provision, section, subsection, clause or phrase of this release is found to be unenforceable or invalid, that portion shall be severed from this contract. The remainder of this contract will then be construed as though the unenforceable portion had never been contained in this document.',
                                                'required'      => true,
                                                'type'          => 'checkbox',
                                        ),
                                );

/* Provisions Foot */
$settings['provisions_foot']    = '';

/* Artist / Practitioner */
$settings['artist_signature']   = true;  // required; autofill planned
$settings['artist_lock']        = true;

/* Two-step form */
$settings['two_step']           = true;   // enables PIN-protected artist step + client link flow

/* Required User Fields */
$settings['require_name']       = true;
$settings['require_address']    = true;
$settings['require_email']      = true;
$settings['require_phone']      = true;

?>
