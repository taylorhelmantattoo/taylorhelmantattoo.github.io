var cam_width = 600;
var cam_height = 450;

var qstr = "";
var passcode="";

var is_safari = /(iPhone|iPod|iPad).*AppleWebKit(?!.*Safari)/i.test(navigator.userAgent);
var is_apple = /(iPhone|iPod|iPad)/i.test(navigator.userAgent);

var url = window.location.href.split('?');
url = url[0];
var autocomplete_threshold=8;
var autocomplete_require_space=1;

function force_guardian()
{
	var parent_input = document.getElementById('parent');
	var parent_require = document.getElementById('require_guardian');

	if(parent_require.value==1) {
		parent_input.style.display='none';
		parent_require.value=0;
	} else {
		parent_input.style.display='block';
		parent_require.value=1;
	}
}

function autocomplete_client(name)
{

	if((!autocomplete_require_space || name.indexOf(' ') >= 0) && name.length >= autocomplete_threshold) {
		var xmlHttp = getXMLHttp();
		xmlHttp.onreadystatechange = function(){
			if(xmlHttp.readyState == 4){
				//check for errors
				if(xmlHttp.responseText.length > 0) {
					var json=JSON.parse(xmlHttp.responseText);

					// normal bits
					if(json.address)	document.getElementById('address').value=json.address;
					if(json.phone)		document.getElementById('phone').value=json.phone;
					if(json.email)		document.getElementById('email').value=json.email;
				
					// date stuffs	
					var dob_split=json.dob.split('-');
					document.getElementById('dobY').value=dob_split[0];
					document.getElementById('dobM').value=dob_split[1].replace(/^0+/, '');
					document.getElementById('dobD').value=dob_split[2].replace(/^0+/, '');
					document.getElementById('dobY').onchange();
					return true;
				}
			}
		}
		xmlHttp.open("POST", url+'?autocomplete='+name, false);

		xmlHttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded")
		xmlHttp.send();
	}
	return false;
}

function todays_date() {
	dom=document.getElementById('todays_date');
	if(dom)
		dom.innerHTML = curDate();
	setTimeout("todays_date()",1000);
}

todays_date();

function lock_down(id)
{
	var ob=document.getElementById(id);

	//inputs
	inputs = ob.getElementsByTagName('input');
	for(a=0;a<inputs.length;a++)
		if(inputs[a].type=='text')
			inputs[a].disabled=true;

	//selects
	inputs = ob.getElementsByTagName('select');
	for(a=0;a<inputs.length;a++)
		inputs[a].disabled=true;

	//signatures
	inputs = ob.getElementsByTagName('canvas');
	for(a=0;a<inputs.length;a++)
		inputs[a].disabled=true;




}

function artist_select(object)
{
	if(object.value == '!other') {
		var newval = window.prompt('Enter artist:', '');
		
		if(newval) {
			var option = document.createElement('option');
			option.value = newval;
			var text = document.createTextNode(newval);
			option.appendChild(text);
			object.appendChild(option);
			object.value = newval;
		} else {
			object.value = -1;
		}
	}
}

function yn_select(id, value)
{
	var yes=document.getElementById('yn_y_'+id);
	var no=document.getElementById('yn_n_'+id);
	var data=document.getElementById('provisions['+id+'][0]');
	
	if(value=='y') {
		yes.src='img/yn_y_on.png';
		no.src='img/yn_n_off.png';
	} else {
		yes.src='img/yn_y_off.png';
		no.src='img/yn_n_on.png';
	}
	data.value=value;

	return false;
}

function canvas_status(id,status){

	var status_box=document.getElementById(id+'_status');
	var value='';

	if(status)
		value='yes';

	if(status_box)
		status_box.value=value
}

function curDate() {
	var today = new Date();
	today=String(today);
	today=today.split('GMT');
	today=today[0];
	return today;
}

function insertDate() {
	document.getElementById('date_data').value = curDate();
}

function age(agelimit){
	var parentDom=document.getElementById('parent');
	var submit=document.getElementById('submit');
	var note=document.getElementById('age_note');

	if(!parentDom)	return false;

	var age_data=document.getElementById('dob_age');
	var age_display=document.getElementById('dob_age_display');

	var year=document.getElementById('dobY').value;
	var month=document.getElementById('dobM').value;
	var day=document.getElementById('dobD').value;

	var today=new Date();
	var this_month=today.getMonth()+1;
	var this_day=today.getDate();

	if(year>0 && month>0 && day>0) {
		// figures out exact age
		var age=today.getFullYear()-year;
		if(this_month<month || (this_month==month && this_day<day)){age--;}
		if(agelimit<0) {
			if(age < -agelimit) {
				note.style.color="#600";
				note.style.textDecoration="underline";
				submit.style.display='none';
			} else {
				note.style.color="";
				note.style.textDecoration="none";
				submit.style.display='block';
			}

		} else {
			if(age < agelimit)
				parentDom.style.display="block";
			else
				parentDom.style.display="none";
		}
	
		age_data.value=age;
		age_display.innerHTML="Age:&nbsp;"+age;
	}
	return false;
} 

function getXMLHttp()
{
	var xmlHttp

	try {
		//Firefox, Opera 8.0+, Safari
		xmlHttp = new XMLHttpRequest();
	}
	catch(e){
		//Internet Explorer
		try{
			xmlHttp = new ActiveXObject("Msxml2.XMLHTTP");
		}
		catch(e){
			try {
				xmlHttp = new ActiveXObject("Microsoft.XMLHTTP");
			}
			catch(e) {
				alert("Your browser does not support AJAX!")
				return false;
			}
		}
	}
	return xmlHttp;
}

function MakeRequest(form)
{
   var passed=true;

   var xmlHttp = getXMLHttp();
   xmlHttp.onreadystatechange = function(){
        if(xmlHttp.readyState == 4){
            //check for errors
	    if(xmlHttp.responseText.length > 0) {
		// full text
		text=xmlHttp.responseText;
		
		// grab first error
		error=text.substr(0, text.indexOf("\n"));

		// remove first error line
		text=text.substring(text.indexOf("\n") + 1);

		popup(text,true,error);
//		popup(text);
		passed=false;
	  }
      }
   }
   var values=getquerystring(form,no_images=true);

   xmlHttp.open("POST", url+'?test=true', false);

   xmlHttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded")
   xmlHttp.send(values);
  
   return passed;
}

function GetElemValue(name, value) {
    qstr += (qstr.length > 0 ? "&" : "")
         + escape(name).replace(/\+/g, "%2B") + "="
         + escape(value ? value : "").replace(/\+/g, "%2B");
}

function getquerystring(form,no_images) {
    qstr = "";
    var elemArray = form.elements;
    for (var i = 0; i < elemArray.length; i++) {
        var element = elemArray[i];
	if(element.type != undefined)
             var elemType = element.type.toUpperCase();
	else
             var elemType = element.tagName.toUpperCase();

	var elemName = element.name;
        if (elemName) {
            if (elemType == "TEXT"
                || elemType == "TEXTAREA"
                || elemType == "PASSWORD"
		|| elemType == "BUTTON"
		|| elemType == "RESET"
		|| elemType == "EMAIL"
		|| elemType == "TEL"
		|| elemType == "SUBMIT"
	    ) {
             	   GetElemValue(elemName, element.value);
	    } else if (elemType == "IMAGE"
		|| elemType == "FILE"
                || elemType == "HIDDEN") {
		    if(no_images!=true || elemName.indexOf("_data") == -1 )
        	     	   GetElemValue(elemName, element.value);
	    }
	    else if (elemType == "CHECKBOX" && element.checked) {
		GetElemValue(elemName, element.value ? element.value : "On");
	    }
            else if (elemType == "RADIO" && element.checked)
                GetElemValue(elemName, element.value);
            else if (elemType.indexOf("SELECT") != -1)
                for (var j = 0; j < element.options.length; j++) {
                    var option = element.options[j];
                    if (option.selected)
                        GetElemValue(elemName,
                            option.value ? option.value : option.text);
                }
        }
    }
    return qstr;
}


function enterpasscode(val)
{
	correctcode=false;
	passcode=passcode+val;

	// trim to last 4 digits
	if(passcode.length>4)
		passcode=passcode.substr(1,4);
	
	// if correct length, check it
	if(passcode.length==4){
		var xmlHttp = getXMLHttp();
		xmlHttp.onreadystatechange = function(){
			if(xmlHttp.readyState == 4)
				if(xmlHttp.responseText == "correct")
					correctcode=true;
		}
   		xmlHttp.open("POST", url+'?test_passcode='+passcode, false);
		xmlHttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded")
		xmlHttp.send();

		return correctcode;
	}
	
	return false;
}

function unhide()
{
	var bg = document.getElementById('container');
	bg.style.display='none';
	
//	var close = document.getElementById('containerClose');
//	close.style.display='none';
}

function hide(text)
{
//	var close = document.getElementById('containerClose');
//	close.style.display='block';

	var bg = document.getElementById('container');

	if(text == undefined)
		text = 'Loading...';

	// background text
	bg.innerHTML='<a href="" onclick="unhide(); popup(); return false;">&larr;</a><div>'+text+'</div>';

	bg.style.display='block';
}

function popup(text,show_button,error)
{
	if(text == undefined)
		var text="";

	// get doms
	var bg = document.getElementById('container');
	var responsediv = document.getElementById('ResponseDiv');
	var response = document.getElementById('Response');
	var close_button = document.getElementById('close_button');
	var close_link = document.getElementById('close_link');

	// close button
	if(show_button == undefined)
		close_button.style.display = 'none';
	else 
		close_button.style.display = 'block';
	// close button link
	if(error != undefined && error != "") {
		if(close_link)
		close_link.onclick=function() {
			popup();
			var jump_to=document.getElementById('input_'+error);
			if(jump_to)
				window.scrollTo(0, jump_to.offsetTop-5);
			return false;
		};
	} else {
		if(close_link)
			close_link.onclick=function() { popup(); return false };
	}

	// fill text
	response.innerHTML = text;

	if(text.length > 0)
		bg.style.display = responsediv.style.display = 'block';
	else
		bg.style.display = responsediv.style.display = 'none';
}

function submit_form() {
	// check if errors occured
	if(MakeRequest(document.forms["MyForm"]))
		return true;
	return false;
}

function display_passcode(){
	document.getElementById('enter_passcode').style.display="block";
	popup("<div class=\"success\">All looks good!<br />Inform your assistant that you are done so they can verify it.</div>", true);
}

function os_camera_upload() {
	var id = 'os_photo';
	var ob = document.getElementById(id);

	// no photo. go away.
	if(!ob) 	return false;
	
	// no photo. go away.
	var file = document.getElementById(id).files[0];
	if(!file)	return false;

	// not image being uploaded
	if (file.type.indexOf("image") == -1) {
//		alert('Please only select an image (not '+file.type+')');
		popup('Please only select an image (not '+file.type+')', true);
		return false;
	}

	hide('Resizing image...');

	// resize image using JS
	if (window.File && window.FileReader && 0) {
		var photo = document.getElementById("os_photo");
		var file = photo.files[0];
	
		var img = document.createElement("img");
		img.width = cam_width;
		img.height = cam_height;
		//document.body.appendChild(img);
	
		var reader = new FileReader();
		reader.onload = function (evt) { 
			img_data=evt.target.result;

			// apple is buggy. 90deg is perfect. -90 is upside down. Rest is distorted and unusable in iOS6.0 - 6.0.1 seems to fix this bug.
			img.src=img_data;
	
			img.onload = function() {
				var canvas = document.createElement("canvas");
				canvas.width=cam_width;
				canvas.height=cam_height;
				//document.body.appendChild(canvas);
				
				var ctx = canvas.getContext("2d");
				
				// flip dat sucker!
				if(is_apple && orientation == -90) {
		             	     	ctx.translate(img.width-1,img.height-1);
					ctx.rotate(Math.PI);
				}

				ctx.drawImage(img, 0, 0, cam_width, cam_height);
					
				img_data = canvas.toDataURL("image/jpeg", '0.75');
				document.getElementById('os_photo_msg').innerHTML = '<img src="'+img_data+'" />';
				unhide();
			}
		}
		reader.readAsDataURL(file);
		return true;
	} else {
		//naughty browsers! (mostly safari). Send to php to return URI
		var fd = new FormData();
		fd.append("image", file);
		
		var xhr = new XMLHttpRequest();
   		xhr.open("POST", url+'?os_submission', false);
	
		xhr.onload = function() {
			img_data = xhr.responseText;
			document.getElementById('photo_data').value = img_data;
			document.getElementById('os_photo_msg').innerHTML = '<img src="'+img_data+'" />';
			unhide();
		}
		xhr.send(fd);
		return true;
	}
	return false;
}

function sendpdf() {
	hide();
	insertDate();
	
	var form = document.forms['MyForm'];
	var canvas = form.getElementsByTagName('canvas');

	// get canvas stuffs
	for(i=0; i<canvas.length; i++) {
		grab_canvas = true;

		// if we don't want to grab camera data, plz dont.
		if(canvas[i].id == 'photo')
			if(cameracontainer = document.getElementById('cameracontainer'))
				if(cameracontainer.style.display == 'none')
					grab_canvas = false;

		if(grab_canvas) {
			document.getElementById(canvas[i].id+'_data').value = canvas[i].toDataURL("image/jpeg", '0.75');
		}
	}

	hide('Emailing...');

	var email_sent=false

	var xmlHttp = getXMLHttp();
	xmlHttp.onreadystatechange = function(){
		if(xmlHttp.readyState == 4){
			error=xmlHttp.responseText;
			if(xmlHttp.responseText == "sent") {
				email_sent=true;
			}
		}
	}
	
	var values=getquerystring(form);

   	xmlHttp.open("POST", url+'?topdf&passcode='+passcode, false);
	xmlHttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded")
	xmlHttp.send(values);
 
	if(email_sent) {
		redirect_time=5;
		refresh_countdown(redirect_time);
		popup("<div class=\"success\">All done and emailed!<br />Refreshing in <span id=\"timeout\">"+redirect_time+"</span> seconds.");

		// play sound
		var ob=document.getElementById('email_sent_notification');
		if(ob) ob.play();
	} else {
		popup('There was an error... whoopsie!<br /><br />'+error, true);
	}

	return email_sent;
}

function refresh_countdown(time) {
	dom = document.getElementById('timeout');
	if(dom)
		dom.innerHTML = time;
	
	if(time==2) {
		// disable reload warning
		window.onbeforeunload = null;
		// refresh by not using history plz
		result = document.getElementById('redirect_url').innerHTML;
		setTimeout("location.replace('"+result+"')",10);
	}

	if(time==0)
		return false;

	time=time-1;
	setTimeout("refresh_countdown("+time+")",1000);
}

// warn before leaving page without submitting
window.onbeforeunload = confirmExit;
function confirmExit()
{
	return "Are you sure you want to leave this release form?";
}
