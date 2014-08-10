<?php	
require('class.googlevoice.php');	//require our class

$gv = new GoogleVoice("your-full-gmail-address@gmail.com", "your-password-or-app-password");//create a new instance
$gv->sms("1234567890", "Message Text"); //send a text

if (stristr($gv->status, "Text sent to")) //check if text was sent
{
	echo "Text sent successfully!"; //tell the user it was sent.
}
else {
	echo "Message Failed! Error - ".$gv->status; //tell the user why it wasnt sent
}

// Get all unread SMSs from your Google Voice Inbox.
$sms = $gv->getUnreadSMS();
$msgIDs = array();
foreach($sms as $s) { //loop through the array provided
	echo $s->phoneNumber.': '.$s->messageText."<br><br>\n"; //display phone number & message on screen
	if(!in_array($s->id, $msgIDs)) { //check if the message is in the ID array
		// google voice wont return any new responses to messages, so we need to delete it from the gvoice inbox
		$gv->deleteMessage($s->id);
		$msgIDs[] = $s->id; //add the ID to the array of ID's
	}
}
?>