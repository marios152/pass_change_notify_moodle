<?php
function randomPassword() {
    $alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789";
    $pass = array(); //remember to declare $pass as an array
    $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
    for ($i = 0; $i < 8; $i++) {
        $n = rand(0, $alphaLength);
        $pass[] = $alphabet[$n];
    }
    return implode($pass); //turn the array into a string
}
?>
<?php
//Reset users password and notify him via email.
	class simplehtml_form2 extends moodleform {
		public function definition() {
			global $CFG;
			$mform = $this->_form; // Don't forget the underscore! 
			$mform->addElement('textarea', 'id', 'id', 'wrap="virtual" rows="5" cols="50"');
			$mform->addElement('passwordunmask', 'password','password'); // third parameter is the text label
			$radioarray=array();
			$radioarray[] =& $mform->createElement('radio', 'language', '', 'English',0,'');
			$radioarray[] =& $mform->createElement('radio', 'language', '', 'Greek',1,'');
			$mform->setDefault('language', 0); // set the default to english
			$mform->addGroup($radioarray, 'radioar', 'language', array(' '), false);
			$this->add_action_buttons($cancel = true, $submitlabel=null);
		}
	}
?>	
<?php
//* Find user, change his password and notify him via email
			$mform = new simplehtml_form2();
			$website = '<yourwebsite>';
			$token="<your token>";
			echo "<h1>Search users change the password and notify</h1>";
			if ($mform->is_cancelled()) {
				//Handle form cancel operation, if cancel button is present on form
				$mform->display();
			}else if ($fromform = $mform->get_data()) {
				//In this case you process validated data. $mform->get_data() returns data posted in form.
				$mform->display();
				$users = explode(",",$fromform->id);
				$formCount=0;
				foreach($users as $user){
				$passGen='';
					$array = array('username'=>$user);//get the usernames
					$result = $DB->get_record('user', $array);
					if($result){ // if user exists
						// update users password and notify.
						//***************************************************************************************************************************************************
						//if password field is left blank then create one random and set it as password
						if (!empty($fromform->password)){
							$URL_Moodle=$site."/webservice/rest/server.php?wstoken=".$token."&wsfunction=core_user_update_users&users[0][id]=".$result->id."&users[0][password]=".$fromform->password;		
						}else{
						// generate random password;
							$passGen = randomPassword();
							$URL_Moodle=$site."/webservice/rest/server.php?wstoken=".$token."&wsfunction=core_user_update_users&users[0][id]=".$result->id."&users[0][password]=".$passGen;
						}
						//open connection
						$ch = curl_init();
						//set the url, number of POST vars, POST data
						curl_setopt($ch,CURLOPT_URL, $URL_Moodle);
						curl_setopt($ch,CURLOPT_RETURNTRANSFER,1); // stop curl from printing the result.
						//curl_setopt($ch, CURLOPT_HTTPHEADER, Array("Content-Type: text/xml"));           //uncomment if it's going to be on platforms behind proxy
						//curl_setopt($ch, CURLOPT_PROXY, "<your proxy>");					  			   //uncomment if it's going to be on platforms behind proxy
						//execute post
						$result = curl_exec($ch);
						//close connection
						curl_close($ch);
						//--------------------------------------------------------------------------SEND EMAIL---------------------------------------------------------------------
						$usernameArray= array('username'=>$user);
						$toUser=$DB->get_record('user', $usernameArray);	 //get info of the user from DB 
						echo "<p>User: ".$toUser->firstname."</p>";
						set_user_preference('auth_forcepasswordchange', true, $toUser->id);//force password change to user on next login.
						$fromUser=$CFG->noreplyaddress;
							switch ($fromform->language){
								case "0": //English
									$subject='Your password has been changed';
									$messageText='';
									$signature ='<br>
												<your signature>
												<br>';
									if (empty($fromform->password)){
									$messageHtml='<div style="font-family:Arial;">
												Hi '.$toUser->firstname.'<br>
												<br>
												Your password has been changed for your account <b>'.$toUser->username.'</b> at '.$website.'<br>
												<br>
												Your temporary password is: <b>'.$passGen.'</b><br>
												<br>
												As soon as you login you will be asked to change the password to something personal.<br>
												'.$signature.'
												</div>';
									}else{
									$messageHtml='<div style="font-family:Arial;">
												Hi '.$toUser->firstname.'<br>
												<br>
												Your password has been changed for your account <b>'.$toUser->username.'</b> at '.$website.'<br>
												<br>
												Your temporary password is: <b>'.$fromform->password.'</b><br>
												<br>
												As soon as you login you will be asked to change the password to something personal.<br>
												'.$signature.'
												</div>';
									}
									break;
								case "1": // Greek
									$subject='Ο κωδικός πρόσβασης έχει αλλάξει';
									$messageText='';
									$signature ='<br>
												<your signature>
												<br>';
									if (empty($fromform->password)){
									$messageHtml='<div style="font-family:Arial;">
												Χαίρεται '.$toUser->firstname.'<br>
												<br>
												Ο κωδικός για το λογαριασμό <b>'.$toUser->username.'</b>, έχει αλλάξει για τη σελίδα '.$website.'<br>
												<br>
												Ο προσωρινός κωδικός πρόσβασης είναι: <b>'.$passGen.'</b><br>
												<br>
												Με τη πρόσβαση σας στη πλατφόρμα θα σας ζητηθεί να αλλάξετε το κωδικό σε ένα της δική σας επιλογής.<br>
												'.$signature.'
												</div>';
									}else{
									$messageHtml='<div style="font-family:Arial;">
												Χαίρεται '.$toUser->firstname.'<br>
												<br>
												Ο κωδικός για το λογαριασμό <b>'.$toUser->username.'</b> έχει αλλάξει για τη σελίδα '.$website.'<br>
												<br>
												Ο προσωρινός κωδικός πρόσβασης είναι: <b>'.$fromform->password.'</b><br>
												<br>
												Με τη πρόσβαση σας στη πλατφόρμα θα σας ζητηθεί να αλλάξετε το κωδικό σε ένα της δική σας επιλογής.<br>
												'.$signature.'
												</div>';
									}
									break;	
							}//end of switch											
						email_to_user($toUser, $fromUser, $subject, $messageText, $messageHtml, ", ", true);
						//---------------------------------------------------------------------------------------------------------------------------------------------------
						echo "<h2>The password has been changed and the student was notified</h2>";
						//***************************************************************************************************************************************************
					}else{
						echo "<p style='color:red;'>This user does not exist: <b>".$user."</b></p>";
					}
					$formCount++;
				}
			} else {
			  // this branch is executed if the form is submitted but the data doesn't validate and the form should be redisplayed
			  // or on the first display of the form.
			 
			  echo "Insert the Student ID separated by a comma and press save changes";
			  //displays the form
			  $mform->display();
			}
		?>	