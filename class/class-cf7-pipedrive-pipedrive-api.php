<?php

/**
* Pipedrive API Wrapper
*
* @since 1.3
*/
class Cf7_Pipedrive_Pipedrive_API {
	
	/**
	 * Pipedrive API.
	 *
	 * @since 1.3
	 * 
	 * @var object
	 */
	protected $pipedrive_api = null;

	public function __construct($pipedrive_api) {
		$this->pipedrive_api = $pipedrive_api;
	}

	public function add_organization($organization) {
		return $this->make_request('organizations', $organization);
	}

	public function add_person($person) {
		return $this->make_request('persons', $person);
	}

	public function add_deal($deal) {
		return $this->make_request('deals', $deal);
	}

	public function get_pipelines() {
		return $this->make_request('pipelines', array(), 'get');
	}

	public function get_stages() {
		return $this->make_request('stages', array(), 'get');
	}

	public function get_users() {
		return $this->make_request('users', array(), 'get');
	}

	public function get_person_fields() {
		return $this->make_request('personFields', array(), 'get');
	}

	public function get_deal_fields() {
		return $this->make_request('dealFields', array(), 'get');
	}

	public function get_organization_fields() {
		return $this->make_request('organizationFields', array(), 'get');	
	}

	public function process_get_request($response) {
		if(isset($response['data'])) {
			$request_data = array();
			foreach ($response['data'] as $data) {
				if($data['name'] != NULL)
					$request_data[] = $data;
			}
			return $request_data;
		}
		return array();
	}

	/**
	 *
	 * @since 1.3
	 *
	 * $type = string;
	 * $additional_data = array();
	 * $request_type = string;
	 * 
	 **/
	public function make_request($type, $object_data = array(), $request_type = 'post') {

		$url = "https://api.pipedrive.com/v1/".$type."?api_token=" . $this->pipedrive_api;
	
		if($request_type == 'post') {
			// Try type without the plural S if there is no data.
			$output = wp_remote_post( $url, array( 'body' => $object_data ) );
		} else {
			$output = wp_remote_get( $url );
		}

		// create an array from the data that is sent back from the API
		if(isset($output['body'])) {
			$result = json_decode($output['body'], 1);
		}

		// Report Errors
		if(isset($result['error'])) {
			// if($this->cf7_pipedrive_debug_mode == 'yes') {
				// trigger_error('PipeDrive Error: Could not add ' . $type . '. MSG: ' . $result['error']);
			// }
		}

		if($request_type == 'get') {
			$result_data = $this->process_get_request($result);
			return $result_data;
		}

		// check if an id came back
		if (!empty($result['data']['id'])) {
			$object_id = $result['data']['id'];
			return $object_id;
		} else {
			return false;
		}
	}

}