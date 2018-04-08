<?php

Class Mitel_ipdect {

    var $host = '';
    var $user = '';
    var $pass = '';
    var $transport = '';

    var $result = false;

    /**
    * @param string $host
    * @param string $user
    * @param string $pass
    * @param string $transport
    */
    public function __construct($host = null, $user = null, $pass = null, $transport = "https")
    {

        if($host == null && $user == null && $pass == null)
        {
            include("config.inc.php");
        }

        $this->host = $host;
        $this->user = $user;
        $this->pass = $pass;
        $this->transport = $transport;
        $this->masterid = $this->get_masterid();
    }

    /**
    * 
    */
    public function get_peers()
    {
        $url = "/GW-DECT/mod_cmd_login.xml?cmd=show&search=&sort=cn&sort-order=up&user=*&xsl=asc_dect_users_right.xsl";

        $peerlist = $this->curl_get_data($url);

        $numberXML = new SimpleXMLElement($peerlist);

        $result = array();
        foreach ($numberXML->user as $record) {
            $cn = (string) $record["cn"];
            $ac = (string) $record->gw["ac"];
            $ipei = (string) $record->gw["ipei"];

            $result[$cn] = array("ac" => $ac, "ipei" => $ipei);
        }

        ksort($result);

        return json_encode($result);
    }

    /**
    * @param string $number
    */
    public function get_peer($number)
    {

        $url = "/GW-DECT/mod_cmd_login.xml?cmd=show&search=" . $number . "&sort=cn&sort-order=up&user=*&xsl=asc_dect_users_left.xsl";

        $peerlist = $this->curl_get_data($url);

        $numberXML = new SimpleXMLElement($peerlist);

        if(!empty($numberXML->user)) {

        } else {
        	throw new Exception('Number not found');
        }

        $result = array(
        	"cn" => (string) $numberXML->user["cn"],
        	"dn" => (string) $numberXML->user["dn"], 
        	"guid" => (string) $numberXML->user["guid"],
        	"usn" => (string) $numberXML->user["usn"],
        	"e164" => (string) $numberXML->user["e164"],
        	"h323" => (string) $numberXML->user["h323"],
        	"pwd" => (string) $numberXML->user["pwd"],
        	"auth" => (string) $numberXML->user["auth"],
        	"ac" => (string) $numberXML->user["ac"]
        );

        return json_encode($result);
    }

    /*
    *
    */
    public function get_masterid()
    {

        $url = "/GW-DECT/mod_cmd_login.xml?cmd=show&xsl=asc_dect_users_left.xsl";

        $curlresult = $this->curl_get_data($url);

        $numberXML = new SimpleXMLElement($curlresult);

        $masterid = (string) $numberXML->{'master-id'}["master-id"];

        return json_encode($masterid);
    }

    /**
    * @param string $cn
    * @param string $dn
    * @param string $h323
    * @param string $e164
    * @param string $auth
    * @param string $pwd
    * @param string $pwd1
    */
    public function set_peer($cn = null, $dn = null, $h323 = null, $e164 = null, $auth = null, $pwd = null, $pwd1 = null)
    {

        $url = "/GW-DECT/mod_cmd_login.xml";

        if(!isset($cn))
        {
        	throw new Exception('Please fill in the CN');
        }
        if(!isset($dn))
        {
        	$dn = $cn;
        }
        if(!isset($h323))
        {
        	$h323 = $number;
        }
       	if(!isset($e164))
       	{
       		$e164 = $number;
       	}
       	if(!isset($auth))
       	{
       		$auth = $number;
       	}

       	if(!isset($pwd) && !isset($pwd1))
       	{
       		$pwd = $number;
       		$pwd1 = $number;
       	} else {
        	if(!$this->check_Compliance($pwd,$pwd1)) {
        		throw new Exception('Passwords do not match');
        	}
        }

        $postdata = array(
            "cn" => $cn,
            "dn" => $dn,
            "h323" => $h323,
            "e164" => $e164,
            "auth" => $auth,
            "pwd" => $pwd,
            "pwd1" => $pwd1,
            "gw.ipei" => "",
            "gw.dsp" => "",
            "gw.ac" => $this->gen_randomAC(),
            "cmd" => "submit-user",
            "xsl" => "asc_dect_edit_user.xml",
            "guid" => "",
            "gw.subs" => "",
            "gw.fc" => "",
            "gw.cki" => "",
            "gw.ciph" => "",
            "gw.master-id" => "",
            "gw.user-assigned" => "false",
            "gw.end" => "",
            "userType" => "user",
            "admin" => "no",
            "save" => "OK",
            "cancel_clicked" => "false"
        );

        $result = $this->curl_post_data($url, $postdata);

        return $result;
    }

    /**
    * @param array $data
    */
    public function edit_peer($data)
    {

        $url = "/GW-DECT/mod_cmd_login.xml";

        $data = json_decode($data, true);

        $postdata = array(
            "cn" => $data["cn"],
            "dn" => $data["dn"],
            "h323" => $data["h323"],
            "e164" => $data["e164"],
            "auth" => $data["auth"],
            "pwd" => $data["pwd"],
            "pwd1" => $data["pwd"],
            "gw.ipei" => $data["ipei"],
            "gw.dsp" => $data["dsp"],
            "gw.ac" => $data["ac"],
            "cmd" => "submit-user",
            "xsl" => "asc_dect_edit_user.xml",
            "guid" => $data["guid"],
            "gw.subs" => "",
            "gw.fc" => "",
            "gw.cki" => "",
            "gw.ciph" => "",
            "gw.master-id" => "",
            "gw.user-assigned" => "false",
            "gw.end" => "",
            "userType" => "user",
            "admin" => "no",
            "save" => "OK",
            "cancel_clicked" => "false"
        );

        $result = $this->curl_post_data($url, $postdata);

        return json_encode($result);
    }

    /**
    * @param string $number
    */
    public function delete_peer($number)
    {

        $url = "/GW-DECT/mod_cmd_login.xml";

        $peer = $this->get_peer($number);

        $peer = json_decode($peer, true);

        $postdata = array(
            "cn" => $peer["cn"],
            "dn" => $peer["dn"],
            "h323" => $peer["h323"],
            "e164" => $peer["e164"],
            "auth" => $peer["auth"],
            "pwd" => $peer["pwd"],
            "pwd1" => $peer["pwd"],
            "gw.ipei" => "",
            "gw.dsp" => "",
            "gw.ac" => $peer["ac"],
            "cmd" => "submit-user",
            "xsl" => "asc_dect_edit_user.xml",
            "guid" => $peer["guid"],
            "gw.subs" => "",
            "gw.fc" => "",
            "gw.cki" => "",
            "gw.ciph" => "",
            "gw.master-id" => "",
            "gw.user-assigned" => "false",
            "gw.end" => "",
            "userType" => "user",
            "admin" => "no",
            "save" => "Delete",
            "cancel_clicked" => "true"
        );

        $curlResult = $this->curl_post_data($url, $postdata);

        $curlResultXML = new SimpleXMLElement($curlResult);

        $curlResult = (string) $curlResultXML["state"];

        if($curlResult === "ok") {
        	$result = array("result" => true);
        } else {
        	$result = array("result" => false);
        }

        return json_encode($result);
    }


    /**
    *
    */
    public function get_radios()
    {
        $url = "/GW-DECT/MASTER/mod_cmd.xml?cmd=xml-radios&xsl=asc_dectmaster_radios.xsl";

        $curlResult = $this->curl_get_data($url);

        $curlResultXML = new SimpleXMLElement($curlResult);

	return json_encode($curlResultXML);

    }

    /*
    *
    */
    public function get_snmpconfig()
    {
        $url = "/SNMP0/mod_cmd.xml?xsl=snmp.xsl";
    }

    /*
    *
    */
    public function set_snmpconfig($data)
    {
        $url = "/SNMP0/mod_cmd.xml";

        $data = json_decode($data, true);

        $postdata = array(
            "community" => $data["community"],
            "name" => $data["name"],
            "contact" => $data["contact"],
            "location" => $data["location"],
            "auth-trap" => $data["auth-trap"],
            "trap-ip-0" => $data["trap-ip-0"],
            "accept-ip-0" => $data["pwd"],
            "accept-mask-0" => $data["ipei"],
            "save" => "OK",
            "cmd" => "form"
        );

        $result = $this->curl_post_data($url, $postdata);

        return json_encode($result);
 
    }

    /*
    *
    */
    public function get_syslogconfig()
    {
        $url = "/LOG0/mod_cmd.xml?xsl=asc_logging.xsl";
    }

    /*
    *
    */
    public function set_syslogconfig($data)
    {
        $url = "/LOG0/mod_cmd.htm";

        $data = json_decode($data, true);

        $postdata = array(
            "community" => $data["community"],
            "name" => $data["name"],
            "contact" => $data["contact"],
            "location" => $data["location"],
            "auth-trap" => $data["auth-trap"],
            "trap-ip-0" => $data["trap-ip-0"],
            "accept-ip-0" => $data["pwd"],
            "accept-mask-0" => $data["ipei"],
            "save" => "OK",
            "cmd" => "form"
        );

        $result = $this->curl_post_data($url, $postdata);

        return json_encode($result);
 
    }


    /**
    *
    */
    public function gen_randomAC()
    {

        $number = rand(0,9999);

        if( $number < 10) {
            $result = "000" . $number;
        } elseif ($number < 100) {
            $result = "00" . $number;
        } elseif ($number < 1000) {
            $result = "0" . $number;
        } else {
            $result = $number;
        }

        return $result;
    }

    /**
    * @param string $urlpath
    */
    public function curl_get_data($urlpath)
    {

        $url = $this->transport . "://" . $this->host . $urlpath;

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_USERPWD => $this->user . ":" . $this->pass,
            CURLOPT_COOKIE => "",
            CURLOPT_HTTPAUTH => CURLAUTH_DIGEST,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0
        ));

        $result = curl_exec($curl);

        return $result;
    }

    /**
    * @param string $urlpath
    * @param array $postdata
    */
    public function curl_post_data($urlpath, $postdata)
    {

        $url = $this->transport . "://" . $this->host . $urlpath;

        $fields_string = "";
        foreach($postdata as $key=>$value)
        {
            $fields_string .= $key.'='.$value.'&';
        }
        rtrim($fields_string, '&');

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_POST => count($postdata),
            CURLOPT_POSTFIELDS => $fields_string,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_USERPWD => $this->user . ":" . $this->pass,
            CURLOPT_COOKIE => "",
            CURLOPT_HTTPAUTH => CURLAUTH_DIGEST,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0
        ));

        $result = curl_exec($curl);

        return $result;
    }

    public function check_compliance($string1, $string2)
    {
    	if($string1 === $string2)
    	{
    		return true;
    	} else {
    		return false;
    	}
    }
}

?>
