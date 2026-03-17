<?php
$autocomplete_file='../autocomplete.inc.php';
$submit=false;
#$submit=true;

// php info
if(isset($_GET['phpinfo'])) {
	phpinfo();
	die;
}

// autocomplete
if(isset($_GET['autocomplete']) && $settings['autocomplete']) {
	if(file_exists($autocomplete_file))
		include_once($autocomplete_file);
	die;
}

//
if(isset($_GET['topdf']) || isset($_GET['debug'])) {
	$submit=true;
}

// ios photo submission, base64 encode and return
if(isset($_REQUEST['os_submission'])) {
//	debug_server();	
	$file = null;
	foreach($_FILES as $_file)
		$file = $_file;

	// resize img
	if($file && $img = file2base64($file,590,442))
		echo $img;
	else
		echo "error... uh oh!";
	die;
}

//
if($_REQUEST['test_passcode']) {
	if(!$settings['enable_passcode'])
		echo "correct";	
		
	if($settings['passcode'] == $_REQUEST['test_passcode'])
		echo "correct";	
	die;
}

//
if($submit && !isset($_GET['debug'])) {
	if(!$settings['enable_passcode'] || $settings['passcode'] == $_REQUEST['passcode']) {
		libxml_use_internal_errors(true); //prevent warnings
		ob_start();		
	}
}

// 
if($_REQUEST['test']) {
//	die;

	$first_error=null;
	$errors=array();

	// artist
	if(is_array($settings['artists']) && count($settings['artists'])>0 && (!$_REQUEST["artist"] || $_REQUEST["artist"] == "-1")) {
		array_push($errors, 'Select your <b>artist</b>');
		if(!$first_error) $first_error = 'artist';
	}

	// fields
	$count=0;
	if($settings['fields']) {
		foreach($settings['fields'] as $field) {
			$required=false;
			if(is_array($field)) {
				$array=$field;
				$field=$array['title'];
				$required=$array['required'];
			}
	
			if($required && strlen($_REQUEST['fields'][$count])==0) {
				array_push($errors, 'You must fill out the <b>'.$field.'</b> field');
				if(!$first_error) $first_error = 'field_'.$count;
			}
			
			$count++;
		}
	}

	// provisions
	$count=0;
	foreach($settings['provisions'] as $prov) {
		if($prov['required']==true || $prov['required']=='on') {
			switch($prov['type']) {
				case 'yn_details':
				case 'yn':
					if(strlen($_REQUEST['provisions'][$count][0])==0) {
						array_push($errors, 'You need to choose yes or no on the <b>'.$prov['title'].'</b> provision');
						if(!$first_error) $first_error = 'provision_'.$count;
					}
					break;
				case 'text':
					if(strlen($_REQUEST['provisions'][$count])==0) {
						array_push($errors, 'You need to answer the <b>'.$prov['title'].'</b> provision');
						if(!$first_error) $first_error = 'provision_'.$count;
					}
					break;
				case 'checklist':
					if(count($_REQUEST['provisions'][$count]) == 0) {
						array_push($errors, 'You need to select atleast one <b>'.$prov['title'].'</b> provision');
						if(!$first_error) $first_error = 'provision_'.$count;
					}
					break;
				case 'dropdown':
					if($_REQUEST['provisions'][$count] == "-1") {
						array_push($errors, 'You need to select the <b>'.$prov['title'].'</b> provision');
						if(!$first_error) $first_error = 'provision_'.$count;
					}
					break;

				default:
					if(!$_REQUEST["provisions"][$count]) {
						array_push($errors, 'You need to check the <b>'.$prov['title'].'</b> provision');
						if(!$first_error) $first_error = 'provision_'.$count;
					}
					break;
			}
		}
		$count++;
	}

	// required fields
	foreach($settings as $set=>$val) {
		$require_string='require_';
		if(substr($set,0,strlen($require_string))==$require_string && $val) {
			$require_sub = substr($set,strlen($require_string));
			if(!$_REQUEST[$require_sub] || $_REQUEST[$require_sub] == "" || $_REQUEST[$require_sub] == "-1") {
				array_push($errors, 'You need to fill out the <b>'.$require_sub.'</b> field');
				if(!$first_error) $first_error = $require_sub;
			}
		}
	}

	// email
	if($_REQUEST['email']) {
		if (!filter_var($_REQUEST['email'], FILTER_VALIDATE_EMAIL)) {
			array_push($errors, 'Invalid <b>email address</b>');
			if(!$first_error) $first_error = 'email';
		}
	}

	// age
	$birthDate=array(
		0=>$_REQUEST['dobM'],
		1=>$_REQUEST['dobD'],
		2=>$_REQUEST['dobY'],
	);

	if($birthDate[0]<0 || $birthDate[1]<0 || $birthDate[2]<0) {
		array_push($errors, 'Please fill out your entire <b>date of birth</b>');
		if(!$first_error) $first_error = 'dob';
	} else {
		$age=dob_to_age($birthDate);	
		if($age<$settings['age_limit']) {
			if(!$_REQUEST['signature_parent_status']) {
				array_push($errors, 'Get your parent/guardian to <b>sign</b> the field');
				if(!$first_error) $first_error = 'parent_signature';
			}

			if(!$_REQUEST['parent_name'] || $_REQUEST['parent_name'] == "") {
				array_push($errors, 'Fill out the <b>parent/guardian name</b>');
				if(!$first_error) $first_error = 'parent_name';
			}
		}  
	}	

	// client signature
	if(!$_REQUEST['signature_client_status']) {
		array_push($errors, 'Sign in the <b>signature field</b>');
		if(!$first_error) $first_error = 'signature';
	}

	// artist signature
	if($settings['artist_signature'])
		if(!$_REQUEST['signature_artist_status']) {
			array_push($errors, 'Please get your <b>artist</b> to sign their <b>signature field</b>');
			if(!$first_error) $first_error = 'artist_signature';
		}
	
	// camera
	if($settings['enable_camera'])
		if($settings['photo_required'])
			if(!$_REQUEST['photo_status']) {
				array_push($errors, 'You need to take a <b>photo of your ID(s)</b>');			
				if(!$first_error) $first_error = 'photo';
			}

	// display errors!
	if($first_error==null) $first_error="nothing";
	if(count($errors)>0) {
		echo $first_error;
		?>

		<h2>Errors</h2>
		<ul class="errors"><?php
			foreach($errors as $error) {
				?><li><?=$error?></li><?php
			}
		?></ul><?php
	}
die;
}

?>
