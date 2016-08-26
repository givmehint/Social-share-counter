<?php
/**
GET SOCIAL COUNT
 */

class getsharecount {
	
	private $url;
	private $timeout;
	public function __construct( $url, $timeout = 10 ) {
		$this->url     = rawurlencode( $url );
		$this->timeout = $timeout;
	}

	/* Share count API for twitter has been disallowed */
	function twitter() { 
		return;
	}

	
	function linkedin() { 
		$json_string = $this->file_get_contents_curl( "http://www.linkedin.com/countserv/count/share?url=$this->url&format=json" );
		$json = json_decode( $json_string, true );
		return isset( $json['count'] ) ? intval( $json['count'] ) : 0;
	}

	
	function facebook() {
		$json_string = file_get_contents('http://graph.facebook.com/?id=' . $this->url);
  		$json = json_decode($json_string, true);
  		return isset($json['share']['share_count']) ? intval($json['share']['share_count']) : 0;
	}

	


	function gplus() {
		$json_string = $this->file_get_contents_curl( 'https://clients6.google.com/rpc', '[{"method":"pos.plusones.get","id":"p","params":{"nolog":true,"id":"'.rawurldecode( $this->url ).'","source":"widget","userId":"@viewer","groupId":"@self"},"jsonrpc":"2.0","key":"p","apiVersion":"v1"}]', array( 'Content-type: application/json' ) );
		$json = json_decode( $json_string, true );
		return isset( $json[0]['result']['metadata']['globalCounts']['count'] ) ? intval( $json[0]['result']['metadata']['globalCounts']['count'] ) : 0;
	}

	
	public function stumble() {
		$json_string = $this->file_get_contents_curl( 'http://www.stumbleupon.com/services/1.01/badge.getinfo?url='.$this->url );
		$json = json_decode( $json_string, true );
		return isset( $json['result']['views'] ) ? intval( $json['result']['views'] ) : 0;
	}

	


	function delicious() {
		$json_string = $this->file_get_contents_curl( 'http://feeds.delicious.com/v2/json/urlinfo/data?url='.$this->url );
		$json = json_decode( $json_string, true );
		return isset( $json[0]['total_posts'] ) ? intval( $json[0]['total_posts'] ) : 0;
	}

	
	



	function pinterest() {
		$return_data = $this->file_get_contents_curl( 'http://api.pinterest.com/v1/urls/count.json?url='.$this->url );
		
		if ( ! is_wp_error( $return_data ) ) {
			$json_string = preg_replace( "/[^(]*\((.*)\)/", "$1", $return_data );
			$json = json_decode( $json_string, true );
		}

		return isset( $json['count'] ) ? intval( $json['count'] ) : 0;
	}







	private function file_get_contents_curl( $url, $post_fields = '', $http_header = array() ) {
		
		

		$max_redirs = (ini_get('open_basedir') == '' && ini_get('safe_mode' == 'Off')) ? 2 : 0;

		$ch = curl_init();

		$opt_arr = array(
			CURLOPT_URL => $url,
			CURLOPT_USERAGENT => $_SERVER['HTTP_USER_AGENT'],
			CURLOPT_FAILONERROR => 1,
			CURLOPT_FOLLOWLOCATION => $max_redirs > 0,
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_TIMEOUT => $this->timeout,			
		);

		if ( ! empty( $post_fields ) )
			$opt_arr[CURLOPT_POSTFIELDS] = $post_fields;

		if ( ! empty( $http_header ) )
			$opt_arr[CURLOPT_HTTPHEADER] = $http_header;

		curl_setopt_array( $ch, $opt_arr );

		$cont = curl_exec( $ch );

		if ( curl_error( $ch ) ) {
			return new WP_Error( 'curl_error', curl_error( $ch ) );
		}

		return $cont;
	}

	



	 function total_shares() {
		$count = 0;

		$fb = $this->facebook();
		$li = $this->linkedin();
		$gp = $this->gplus();
		$dl = $this->delicious();
		$st = $this->stumble();
		$pi = $this->pinterest();

		$count = $fb + $li + $gp + $dl + $st + $pi;

		return $count;
	}
}
?>
