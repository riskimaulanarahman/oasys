<?php
class LDAP{
	public $server;
	public $domain;
	public $username;
	public $password;
	public $conn;
	public $error;
	public $headers;
	private $mssql;
	public function __construct($server="",$domain="",$username="",$password=""){
		$this->server = $server;
		$this->domain = $domain;
		$this->username = $username;
		$this->password = $password;
	}
	private function get_msg_str($msg, $start, $unicode = true) {
		$len = (ord($msg[$start+1]) * 256) + ord($msg[$start]);
		$off = (ord($msg[$start+5]) * 256) + ord($msg[$start+4]);
		if ($unicode)
			return str_replace("\0", '', substr($msg, $off, $len));
		else
			return substr($msg, $off, $len);
	}
	private function cleanUpEntry( $entry ) {
		$retEntry = array();
		for ( $i = 0; $i < $entry['count']; $i++ ) {
			if (is_array($entry[$i])) {
				$subtree = $entry[$i];
				if ( ! empty($subtree['dn']) and ! isset($retEntry[$subtree['dn']])) {
					$retEntry[$subtree['dn']] = $this->cleanUpEntry($subtree);
				} else {
					$retEntry[] = $this->cleanUpEntry($subtree);
				}
			} else {
				$attribute = $entry[$i];
				if ( $entry[$attribute]['count'] == 1 ) {
					$retEntry[$attribute] = $entry[$attribute][0];
				} else {
					for ( $j = 0; $j < $entry[$attribute]['count']; $j++ ) {
						$retEntry[$attribute][] = $entry[$attribute][$j];
					}
				}
			}
		}
		return $retEntry;
	}
	public function connect(){
		$this->conn = ldap_connect($this->server );
		$this->error = ldap_error($this->conn);
		return $this->conn;
	}
	public function bind(){
		if ($this->conn) {
			ldap_set_option($this->conn, LDAP_OPT_REFERRALS, 0);
			ldap_set_option($this->conn, LDAP_OPT_PROTOCOL_VERSION, 3);
			//return ldap_bind($this->conn, $this->domain."\\".$this->username, $this->password);	
			$bind = ldap_bind($this->conn, $this->domain."\\".$this->username, $this->password);
			$this->error = ldap_error($this->conn);
			if ($this->error=="Success"){
				return true;
			}else{
				return false;
			}								
		}else{
			$this->error = ldap_error($this->conn);	
			return false;
		}
	}
	function getUser($base_dn,$filter,$justthese=array(),$pageSize=100){
		$cookie = '';
		$user = array();
		do {
			ldap_control_paged_result($this->conn, $pageSize, true, $cookie);
			$result  = ldap_search($this->conn, $base_dn, $filter, $justthese);
			$entries = ldap_get_entries($this->conn, $result);
			if(!empty($entries)){
				$resultx = $this->cleanUpEntry($entries);
				array_push($user,$resultx);
			}
			ldap_control_paged_result_response($this->conn, $result, $cookie);
		} while($cookie !== null && $cookie != '');	
		return $user;
	}
}
?>