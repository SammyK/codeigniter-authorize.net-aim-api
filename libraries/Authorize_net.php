<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Authorize.net AIM Integration
 *
 * For Authorize.net AIM integration
 *
 * @package        	CodeIgniter
 * @subpackage    	Libraries
 * @category    	Libraries
 * @author		SammyK (http://sammyk.me/)
 * @link		https://github.com/SammyK/codeigniter-authorize.net-aim-api
 */
class Authorize_net
{
    private $CI;						// CodeIgniter instance

    private $api_login_id = '';			// API Login ID
    private $api_transaction_key = '';	// API Transaction Key
    private $api_url = '';				// Where we postin' to?
	
    private $post_vals = array();		// Values that get posted to Authroize.net
	
	/*
	 * If your installation of cURL works without the "CURLOPT_SSL_VERIFYHOST"
	 * and "CURLOPT_SSL_VERIFYPEER" options disabled, then remove them
	 * from the array below for better security.
	 */
    private $curl_options = array(		// Additional cURL Options
		CURLOPT_SSL_VERIFYHOST => 0,
		CURLOPT_SSL_VERIFYPEER => 0,
		);
	
    private $response = '';				// Response from Authorize.net
    private $transation_id = '';		// The transation ID from Authorize.net
    private $approval_code = '';		// The approval code from Authorize.net
	
    private $error = '';				// Error to show to the user

	public function __construct( $config = array() )
	{
		$this->CI =& get_instance();
		
		// Load config file
		$this->CI->config->load('authorize_net', TRUE);
		
		foreach( $this->CI->config->item('authorize_net') as $key => $value )
		{
			if( isset($this->$key) )
			{
				$this->$key = $value;
			}
		}

		$this->initialize($config);
	}

	// Initialize the lib
	public function initialize( $config )
	{
		foreach( $config as $key => $value )
		{
			if( isset($this->$key) )
			{
				$this->$key = $value;
			}
		}
	}
	
	// Set the data that we're going to send
	public function setData( $data )
	{
		$this->post_vals = $data;
	}
	
	// Get the values we're going to send
	public function getPostVals()
	{
		$auth_net_vals = array(
			'x_login'				=> $this->api_login_id,
			'x_tran_key'			=> $this->api_transaction_key,
			'x_version'				=> '3.1',
			'x_delim_char'			=> '|',
			'x_delim_data'			=> 'TRUE',
			'x_type'				=> 'AUTH_CAPTURE',
			'x_method'				=> 'CC',
			'x_relay_response'		=> 'FALSE',
			);
		
		return array_merge($auth_net_vals, $this->post_vals);
	}
	
	// Authorize and capture a card
	public function authorizeAndCapture()
	{
		// Load cURL lib
		$this->CI->load->library('curl');
		$this->response = $this->CI->curl->simple_post(
				$this->api_url,
				$this->getPostVals(),
				$this->curl_options);
		
		return $this->parseResponse($this->response);
	}
	
	// Parse the response back from Authorize.net
	public function parseResponse( $response )
	{
		if( $response === FALSE )
		{
			$this->error = 'There was a problem while contacting the payment gateway. Please try again.';
			return FALSE;
		}
		elseif( is_string($response) && strpos($response, '|') !== FALSE )
		{
			$res = explode('|', $response);
			
			if( isset($res[0]) )
			{
				switch( $res[0] )
				{
					case '1': // Approved
					$this->transation_id = isset($res[6]) ? $res[6] : '';
					$this->approval_code = isset($res[4]) ? $res[4] : '';
					return TRUE;
					break;
				
					case '2': // Declined
					case '3': // Error
					case '4': // Held for Review
					if( isset($res[3]) )
					{
						$this->error = $res[3];
					}
					return FALSE;
					break;
				
					default: // ??
					break;
				}
			}
			else
			{
				$this->error = 'There was a problem while contacting the payment gateway. Please try again.';
				return FALSE;
			}
		}
		
		$this->error = 'Received an unknown response from the payment gateway. Please try again.';
		return FALSE;
	}
	
	// Get the transation ID
	public function getTransactionId()
	{
		return $this->transation_id;
	}
	
	// Get the transation code
	public function getApprovalCode()
	{
		return $this->approval_code;
	}
	
	// Get the error text
	public function getError()
	{
		return $this->error;
	}
	
	// Dump some debug data to the screen
	public function debug()
	{
		echo "<h1>Authorize.NET AIM API</h1>\n";
		$url = $this->CI->curl->debug_request();
		echo "<p>URL: " . $url['url'] . "</p>\n";
		echo "<h3>Response</h3>\n";
		echo "<code>" . nl2br(htmlentities($this->response)) . "</code><br/>\n\n";
		echo "<hr>\n";

		if( $this->CI->curl->error_string )
		{
			echo "<h3>cURL Errors</h3>";
			echo "<strong>Code:</strong> " . $this->CI->curl->error_code . "<br/>\n";
			echo "<strong>Message:</strong> " . $this->CI->curl->error_string . "<br/>\n";
			echo "<hr>\n";
		}

		echo "<h3>cURL Info</h3>";
		echo "<pre>";
		print_r($this->CI->curl->info);
		echo "</pre>";
	}
	
	// Reset everything so we can try again
	public function clear()
	{
		$this->response = '';
		$this->transation_id = '';
		$this->approval_code = '';
		$this->error = '';
		$this->post_vals = array();
	}

}

/* EOF */