<?
$settings=array();

/* Release Info */
$settings['name']		= 'Body Piercing';
$settings['header']		= '';

/* Additional Fields */
$settings['fields']		= array(
					array(
						'title'		=> $settings['name'].' Location',
						'required'	=> true,
					),
					array(
						'title'		=> $settings['name'].' Price',
						'required'	=> true,
					),
				);

/* Email Stuff */
$settings['business_name']	= $settings['name'].' Studio';
$settings['email_to']		= 'dev@null.com';
$settings['email_from']		= 'dev@null.com';
$settings['email_subject']	= 'Your '.$settings['name'].' release form';
$settings['email_text']		= 'Attached is your release form for your '.strtolower($settings['name']).'

Here is some aftercare, just in case you lost your brochure!

HEALING
    Week 1 - Inflammatory. Swelling, redness and clear fluid are normal. Ice ice baby!
    Week 2-4 - Healing. Yellow \'crusties\' form, don\'t pick \'em! Begin to warm soak.
    Week 5 - Maturation. Redness should be mostly gone. Still clean the piercing.
    Week 6 - Downsizing. Come in because we may need to change the jewellery.

ORAL PIERCING
    Use alcohol-free mouth rinse 2x a day (maximum!!). Oral-B is yummy.
    Follow FACIAL aftercare for lip piercings.
    Ice and cold drinks during healing.
    Swelling is normal for 2 weeks.
    Eat soft foods during initial healing.
    Rinse with cold water after every meal.
    Do NOT play with jewellery at all.
    Downsize jewellery in 4-6 weeks.
    Healing can take up to 3 months.

FACIAL & TORSO
    Soak 2x a day with warm saline solution (contact lense cleaner is the same thing).
    1/2 fill and flip a shot-glass for navel/nipple, Elsewhere soak with make-up remover pads.
    Alternatively you can also spray 4x daily with H2Ocean saline spray.
    Be sure to rinse off excess saline in shower.
    Do not turn/twist jewellery and DON\'T pick at your damn crusties.

GENITAL
    Follow TORSO aftercare.
    Bleeding is normal for a week.
    No unprotected sex during healing! ; )
    Sex can resume when comfortable (4-6w)
    Shower before/after sex during healing.
    Jewellery downsize after 6 weeks.
    Piercing can take up to 6 months to comfortably heal and settle in.

ANCHORS
    Ice for first 24-48hrs.
    Lasonil anti-bruising cream on red areas. 2x a day for 3 days works well.
    Don\'t pick crusties or blood.
    Let dry out, don\'t cover.
    Be very careful not to catch!
    Anchor\'s heal in 2-3 weeks.
    Don\'t change the head yourself.

SURFACE
    Follow TORSO aftercare.
    Many surface piercings can take up to 6-8 months to fully heal.
    Be careful not to bump or catch.

TROUBLES
    Gold and sterling silver are not good for the first 6 months.
    Sleeping on or bumping your piercing will slow healing and cause lumps.
    Hot chamomile tea bag compress soaks reduce bumps and increases blood flow.
    Oral piercings that are constantly played with will rip and cause white bumpy scar tissue. Saline rinses, Aspro Clear and honey can help to reduce bumps.
    Using alcohol wipes, Dettol, betadine, listerine or any harsh products will harm your piercing. Don\'t friggin\' use em!';

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
$settings['artists']		= array(
					'Percing Man',
					'The dude with the big ears',
					'The chick',
				);
$settings['allow_other_artist']	= true;

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
						'title'		=>'Bloodbourne Pathogens',
						'text'		=>'Do you have any bloodbourne pathogens, transmittable diseases or recent illnesses? (It\' okay if you do, we just want to know for our and other\'s safety).',
						'required'	=>true,
						'type'		=>'yn',
					),
					array(
						'title'		=>'Risks',
						'text'		=>'That I have been fully informed of the risks, associated with getting a piercing. I understand that these risks, known and unknown, can lead to injury, including but not limited to infection, scarring and keloiding and allergic reactions. Having been informed of the potential risks associated with getting a piercing, I still wish to proceed with the piercing and I freely accept all risks that may arise from piercing.',
						'required'	=>true,
					),
					array(
						'title'		=>'Release',
						'text'		=>'TO WAIVE AND RELEASE to the fullest extent permitted by law each of the Artist and the Piercing Studio from all liability whatsoever, for any and all claims or causes of action that I, my estate, heirs, executors or assigns may have for personal injury or otherwise, including any direct and/or consequential damages, which result or arise, whether caused by the negligence or fault of either the Artist or the Piercing Studio, or otherwise.',
						'required'	=>true,
					),
					array(
						'title'		=>'Questions',
						'text'		=>'That both the Artist and the Piercing Studio have given me the full opportunity to ask any and all questions about the piercing procedure and the they have been answered to my total satisfaction.',
						'required'	=>true,
					),
					array(
						'title'		=>'Aftercare',
						'text'		=>'I affirm that I have given me instructions on the care of my piercing while it.s healing, and I understand them and will follow them. I acknowledge that it is possible that the piercing can become infected, particularly if I do not follow the instructions.',
						'required'	=>true,
					),
					array(
						'title'		=>'Duress',
						'text'		=>'I affirm that I am not under the influence of alcohol or drugs, and I am voluntarily getting a piercing without duress.',
						'required'	=>true,
					),
					array(
						'title'		=>'Medical Conditions',
						'text'		=>'I affirm that I do not have diabetes, epilepsy, hemophilia, nor do I have a heart condition or take blood thinning medication. I do not have any other medical or skin condition that may interfere with the procedure or healing of the piercing. I am not the recipient of an organ or bone marrow transplant or, if I am, I have taken the prescribed preventive regimen of anti-biotics that is required by my doctor in advance of any invasive procedure such as piercing. I am not pregnant or nursing.',
						'required'	=>true,
					),
					array(
						'title'		=>'Permanent change',
						'text'		=>'I acknowledge that the piercing will result in a permanent change to my appearance and that my skin may not be restored to its pre-piercing condition even after its removal.',
						'required'	=>true,
					),
					array(
						'title'		=>'This Document',
						'text'		=>'I acknowledge that I have been given adequate opportunity to read and understand this document, that it was not presented to me at the last minute, and I understand that I am signing a legal contract.',
						'required'	=>true,
					),
					array(
						'title'		=>'Attourney Fees',
						'text'		=>'I agree to reimburse each of the Artist and the Piercing Studio for any attorneys. fees and costs incurred in any legal action I bring against either the Artist or the Piercing Studio and in which either the Artist or the Piercing Studio is the prevailing party. I agree that the that the courts of NSW in Australia shall have personal jurisdiction and venue over me and shall have exclusive jurisdiction for the purpose of litigating any dispute arising out of or related to this agreement.',
						'required'	=>true,
					),
					array(
						'title'		=>'Photography',
						'text'		=>'I release all rights to any photographs taken of me and the piercing and give consent in advance to their reproduction in print or electronic form.',
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
