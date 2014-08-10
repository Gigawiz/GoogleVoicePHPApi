<?PHP
/*
Version     0.2
License     This code is released under the MIT Open Source License. Feel free to do whatever you want with it.
Author      harmfulmonk@gmail.com, http://www.steamtotext.mobi/
LastUpdate  08/09/2014
*/
class GoogleVoice
{
    public $username;
    public $password;
    public $status;
    private $lastURL;
    private $login_auth;
    private $inboxURL = 'https://www.google.com/voice/m/';
    private $loginURL = 'https://www.google.com/accounts/ClientLogin';
    private $smsURL = 'https://www.google.com/voice/m/sendsms';
	private $smsReadUrl = 'https://www.google.com/voice/inbox/recent/sms/';
	private $markAsReadUrl = 'https://www.google.com/voice/inbox/mark/';
	private $deleteMessageUrl = 'https://www.google.com/voice/inbox/deleteMessages/';

    public function __construct($username, $password)
    {
        $this->username = $username;
        $this->password = $password;
    }

    public function getLoginAuth()
    {
        $login_param = "accountType=GOOGLE&Email={$this->username}&Passwd={$this->password}&service=grandcentral&source=com.lostleon.GoogleVoiceTool";
        $ch = curl_init($this->loginURL);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (iPhone; U; CPU iPhone OS 2_2_1 like Mac OS X; en-us) AppleWebKit/525.18.1 (KHTML, like Gecko) Version/3.1.1 Mobile/5H11 Safari/525.20");
        curl_setopt($ch, CURLOPT_REFERER, $this->lastURL);
        curl_setopt($ch, CURLOPT_POST, "application/x-www-form-urlencoded");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $login_param);
        $html = curl_exec($ch);
        $this->lastURL = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        curl_close($ch);
        $this->login_auth = $this->match('/Auth=([A-z0-9_-]+)/', $html, 1);
        return $this->login_auth;
    }

    public function get_rnr_se()
    {
        $this->getLoginAuth();
        $ch = curl_init($this->inboxURL);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $headers = array("Authorization: GoogleLogin auth=".$this->login_auth, 'User-Agent: Mozilla/5.0 (iPhone; U; CPU iPhone OS 2_2_1 like Mac OS X; en-us) AppleWebKit/525.18.1 (KHTML, like Gecko) Version/3.1.1 Mobile/5H11 Safari/525.20');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $html = curl_exec($ch);
        $this->lastURL = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        curl_close($ch);
        $_rnr_se = $this->match('!<input.*?name="_rnr_se".*?value="(.*?)"!ms', $html, 1);
        return $_rnr_se;
    }

    public function sms($to_phonenumber, $smstxt)
    {
        $_rnr_se = $this->get_rnr_se();
        $sms_param = "id=&c=&number=".urlencode($to_phonenumber)."&smstext=".urlencode($smstxt)."&_rnr_se=".urlencode($_rnr_se);
        $ch = curl_init($this->smsURL);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $headers = array("Authorization: GoogleLogin auth=".$this->login_auth, 'User-Agent: Mozilla/5.0 (iPhone; U; CPU iPhone OS 2_2_1 like Mac OS X; en-us) AppleWebKit/525.18.1 (KHTML, like Gecko) Version/3.1.1 Mobile/5H11 Safari/525.20');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_REFERER, $this->lastURL);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $sms_param);      
        $this->status = curl_exec($ch);
        $this->lastURL = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        curl_close($ch);
        return $this->status;
    }
		/**
	 * Get all of the unread SMS messages in a Google Voice inbox.
	 */
	public function getUnreadSMS() {
		//ini_set ("display_errors", "1");
		//error_reporting(E_ALL);
		// Login to the service if not already done.
		$this->getLoginAuth();

		// Send HTTP POST request.
		$ch = curl_init($this->smsReadUrl);
		curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
        $headers = array("Authorization: GoogleLogin auth=".$this->login_auth, 'User-Agent: Mozilla/5.0 (iPhone; U; CPU iPhone OS 2_2_1 like Mac OS X; en-us) AppleWebKit/525.18.1 (KHTML, like Gecko) Version/3.1.1 Mobile/5H11 Safari/525.20');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_REFERER, $this->lastURL);
		curl_setopt($ch, CURLOPT_POST, FALSE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		$this->lastURL = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
		$xml = curl_exec($ch);
        curl_close($ch);
		$cleaned_xml = substr($xml, 0, strpos($xml, "<html>"));
		$cleaned_xml = substr($cleaned_xml, strpos($cleaned_xml, "<json>"));
		$cleaned_xml = str_replace("<json>", "", $cleaned_xml);
		$cleaned_xml = str_replace("</json>", "", $cleaned_xml);
		$cleaned_xml = str_replace("<![CDATA[", "", $cleaned_xml);
		$cleaned_xml = str_replace("]]>", "", $cleaned_xml);
		//decode the cleaned json
		$json = json_decode($cleaned_xml);
		//var_dump($json->messages);
		// Loop through all of the messages.
		$results = array();
		foreach ($json->messages as $mid=>$convo)
		{
			if ($convo->isRead == false)
			{
				//echo $convo->id;
				$results[] = $convo;
				//var_dump($results);
			}
		}
		return $results;
	}
	/**
	 * Mark a message in a Google Voice Inbox or Voicemail as read.
	 * @param $message_id The id of the message to update.
	 * @param $note The message to send within the SMS.
	 */
	public function markMessageRead($message_id) {
		$_rnr_se = $this->get_rnr_se();
		// Login to the service if not already done.
		$this->getLoginAuth();
		// Send HTTP POST request.
		$ch = curl_init($this->markAsReadUrl);
		curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
        $headers = array("Authorization: GoogleLogin auth=".$this->login_auth, 'User-Agent: Mozilla/5.0 (iPhone; U; CPU iPhone OS 2_2_1 like Mac OS X; en-us) AppleWebKit/525.18.1 (KHTML, like Gecko) Version/3.1.1 Mobile/5H11 Safari/525.20');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_REFERER, $this->lastURL);
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, array(
			'_rnr_se' => $_rnr_se,
			'messages' => $message_id,
			'read' => '1'
			));
		$this->lastURL = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
		curl_exec($ch);
		curl_close($ch);
		
	}
	
		/**
	 * Delete a message or conversation.
	 * @param $message_id The ID of the conversation to delete.
	 */

	public function deleteMessage($message_id) {
		$rnr_se = $this->get_rnr_se();
		$this->getLoginAuth();
		echo $rnr_se."<br>";
		echo $message_id;
		$sms_param = "messages=".urlencode($message_id)."&_rnr_se=".urlencode($_rnr_se)."&trash=1";
		$ch = curl_init($this->$deleteMessageUrl);
		curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
        $headers = array("Authorization: GoogleLogin auth=".$this->login_auth, 'User-Agent: Mozilla/5.0 (iPhone; U; CPU iPhone OS 2_2_1 like Mac OS X; en-us) AppleWebKit/525.18.1 (KHTML, like Gecko) Version/3.1.1 Mobile/5H11 Safari/525.20');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $sms_param);    
		$this->lastURL = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
		$this->status = curl_exec($ch);
		curl_close($ch);
	}
	
    private function match($regex, $str, $out_ary = 0)
    {
        return preg_match($regex, $str, $match) == 1 ? $match[$out_ary] : false;
    }
}
?>
