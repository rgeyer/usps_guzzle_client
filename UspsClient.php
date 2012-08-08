<?php
// Copyright 2012 Ryan J. Geyer
// Licensed under the Apache License, Version 2.0 (the "License");
// you may not use this file except in compliance with the License.
// You may obtain a copy of the License at

// http://www.apache.org/licenses/LICENSE-2.0

// Unless required by applicable law or agreed to in writing, software
// distributed under the License is distributed on an "AS IS" BASIS,
// WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
// See the License for the specific language governing permissions and
// limitations under the License.

namespace Guzzle\Usps;

use Guzzle\Http\Plugin\ExponentialBackoffPlugin;

use Guzzle\Http\Plugin\CookiePlugin;

use Guzzle\Service\Inspector;
use Guzzle\Http\Message\RequestInterface;
use Guzzle\Service\Client;
use Guzzle\Service\Description\XmlDescriptionBuilder;

class UspsClient extends Client {

	protected $username;
	
	protected $package_exceptions;
	
	/**
	 * Factory method to create a new UspsClient
	 *
	 * @param array|Collection $config Configuration data. Array keys:
	 * base_url - Base URL of web service
	 * username - Your USPS username
	 * package_exceptions - A boolean indicating if exceptions should be thrown for package exceptions. Defaults to true
	 *
	 * @return UspsClient
	 *
	 * @TODO update factory method and docblock for parameters
	 */
	public static function factory($config = array()) {
		$default = array ('base_url' => 'http://production.shippingapis.com/', 'package_exceptions' => true);
		$required = array ('username', 'base_url');
		$config = Inspector::prepareConfig ( $config, $default, $required );
		
		$client = new self ( $config->get( 'base_url' ), $config->get('username'), $config->get('package_exceptions')	);
		$client->setConfig ( $config );
		
		// Retry 50x responses
		$client->getEventDispatcher()->addSubscriber(new ExponentialBackoffPlugin());

		return $client;
	}
	
	/**
	 * 
	 * @param unknown_type $base_url
	 */
	public function __construct($base_url, $username, $package_exceptions) {
		parent::__construct($base_url);

		$this->username = $username;
		$this->package_exceptions = $package_exceptions;
	}
	
	public function getUsername() {
		return $this->username;
	}
	
	public function getPackageExceptions() {
		return $this->package_exceptions;		
	}
	
	/**
	 * TODO: Should I be setting this somewhere in the parent Client class?
	 * @param boolean $bool
	 */
	public function setPackageExceptions($bool) {
		$this->package_exceptions = $bool;
	}
}