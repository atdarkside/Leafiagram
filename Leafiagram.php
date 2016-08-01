<?php

class Leafiagram {

	public $username;
	public $password;
	public $sigKey;

	public $userAgent;
	public $guid;
	public $device_id;
	public $token;
	public $username_id;
    public $rank_token;

	public function __construct($username, $password)
  	{
      $this->username = $username;
      $this->password  = $password;

      //set
      $this->sigKey = "b5d839444818714bdab3e288e6da9b515f85b000b6e6b452552bfd399cb56cf0";
      $this->userAgent = "Instagram 8.5.1 Android (18/4.3; 320dpi; 720x1280; Fly; IQ4410i; Phoenix 2; qcom; en_US)";
      $this->guid = $this->make_guid(true);
      $this->device_id = "android-".$this->guid;



  	}

	public function login(){

		$p_token = $this->sendReq('si/fetch_headers/?challenge_type=signup&guid='.$this->make_guid(false), null);
    preg_match('#Set-Cookie: csrftoken=([^;]+)#', $p_token[0], $token);

		$login_data = json_encode(array(
              "_csrftoken"=>$token[0],
      				"username" => $this->username,
      				"password" => $this->password,
      				"guid"     => $this->guid,
      				"device_id"=> $this->device_id,
      				"Content-Type" => "application/x-www-form-urlencoded; charset=UTF-8"
      				));
      $res = $this->sendReq('accounts/login/',$login_data);

      preg_match('#Set-Cookie: csrftoken=([^;]+)#', $res[0], $match_res);
      $this->token = $match_res[1];
      $this->username_id = $res[1]["logged_in_user"]["pk"];
      $this->rank_token = $this->username_id."_".$this->guid;

      return $res;
	}

    /*
    // like
    //      API
    */
	public function addLike($media_id){
		$data = json_encode([
        		"_uuid"      => $this->guid,
        		"_uid"       => $this->username_id,
        		"_csrftoken" => $this->token,
        		"media_id"   => $media_id
    	]);

        return $this->sendReq("media/{$media_id}/like/", $data)[1];
	}

    /*
    // getPost
    //      API
    */
    public function feed(){
        return $this->sendReq("feed/timeline/")[1];
    }

    public function home(){
        return $this->sendReq("feed/timeline/?rank_token=$this->rank_token&ranked_content=true&max_id=100")[1];
    }

    public function tagsearch($word){
        return $this->sendReq("feed/tag/$word/?rank_token=$this->rank_token&ranked_content=true")[1];
    }

    /*
    // Folllow
    //      API
    */
    public function getFollower($username_id) {
        return $this->sendReq("friendships/$username_id/followers/?ig_sig_key_version=4&rank_token=$this->rank_token")[1];
    }

    public function getFollowing($username_id) {
        return $this->sendReq("friendships/$username_id/following/?ig_sig_key_version=4&rank_token=$this->rank_token")[1];
    }



	public function sendReq($url,$postdata = null){
    $req_header = $this->make_header($postdata);

		$headers = [
        	'Connection: close',
        	'Accept: */*',
        	'Content-type: application/x-www-form-urlencoded; charset=UTF-8',
       		'Cookie2: $Version=1',
        	'Accept-Language: en-US',
    	];
		$fullUrl = "https://i.instagram.com/api/v1/".$url;
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $fullUrl);
		curl_setopt($ch, CURLOPT_USERAGENT, $this->userAgent);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_HEADER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_VERBOSE, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_COOKIEFILE, $this->username.'-cookies.text');
        curl_setopt($ch, CURLOPT_COOKIEJAR,$this->username.'-cookies.text');
		if($postdata){
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $req_header);
		}
		
		$res = curl_exec($ch);
        $header_len = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header = substr($res, 0, $header_len);
        $body = substr($res, $header_len);

        curl_close($ch);
        return [$header,json_decode($body, true)];
	}


	public function make_header($req){
		$req = str_replace("'",'"',$req);
		$sig = hash_hmac('sha256', $req, $this->sigKey, false);
		return 'ig_sig_key_version=4&signed_body='.$sig.'.'.str_replace("+","%20",str_replace("-","%2D",urlencode($req)));
	}

	public function make_guid($tf) {
    	$data = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x', 
			mt_rand(0, 30000), 
            mt_rand(0, 30000), 
            mt_rand(0, 30000), 
            mt_rand(17388, 78787), 
            mt_rand(34534, 69899), 
            mt_rand(0, 30000), 
            mt_rand(0, 30000), 
            mt_rand(0, 30000)
          );
    	return $tf ? $data : str_replace('-', '', $data);
    }
}