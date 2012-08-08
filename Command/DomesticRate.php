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

namespace Guzzle\Usps\Command;

use Guzzle\Common\Exception\InvalidArgumentException;

use Guzzle\Http\Exception\BadResponseException;

use Guzzle\Service\Command\AbstractCommand;
use Guzzle\Usps\Common\DomesticRateException;

/**
 * Sends Domestic (V4) Rate requests to the USPS rate API
 * 
 * Validates that at least one package has been specified, but required params for each package are not validated.
 * Please refer to the API documentation for required fields.
 * https://www.usps.com/webtools/htm/Rate-Calculators-v1-5.htm 
 * 
 * @guzzle packages doc="An array of one or more packages to retrieve rate quotes for" required="true" type="array"
 * 
 * @author Ryan J. Geyer <me@ryangeyer.com>
 */
class DomesticRate extends AbstractCommand {
	protected $_package_param_order = array(
		'Service',
		'FirstClassMailType',
		'ZipOrigination',
		'ZipDestination',
		'Pounds',
		'Ounces',
		'Container',
		'Size',
		'Width',
		'Height',
		'Girth',
		'Value',
		'AmountToCollect',
		'SpecialServices', # TODO: This needs more processing, since it is an array
		'SortBy',
		'Machinable',
		'ReturnLocations',
		'ReturnServiceInfo',
		'ShipDate' # TODO: This needs more processing, since it can have an attribute
	);
	
	protected function build() {
		$this->request = $this->client->get('/ShippingAPI.dll');
		$this->request->getQuery()->set('API', 'RateV4');		
		$username = $this->client->getUsername();
		$packages = $this->get('packages');
		
		if(count($packages) == 0) {
			throw new InvalidArgumentException("You must specify at least one package");
		}
		
		$xml = new \SimpleXMLElement('<?xml version="1.0" ?><RateV4Request></RateV4Request>');
		$xml->addAttribute('USERID', $username);
		$xml->Revision = 2;
		
		foreach($packages as $idx => $package) {
			$id = $idx;
			if(array_key_exists('ID', $package)) {
				$id = $package['ID'];
				unset($package['ID']);
			}
			
			$xml_package = $xml->addChild('Package');
			$xml_package->addAttribute('ID', $id);
			
			# The USPS API is a psuedo SOAP API, and the order of the nodes inside
			# of the <Package> node is enforced.  This insulates the user from having
			# to supply the parameters in that specific order.
			foreach($this->_package_param_order as $param) {
				if(array_key_exists($param, $package)) {
					$xml_package->{$param} = $package[$param];
				}
			}
		}
		
		$this->request->getQuery()->set('XML', $xml->asXML());
		
	}
	
	/**
	 * {@inheritdoc}
	 * @return SimpleXMLElement
	 * @throws BadResponseException If the API response is not an XML document
	 * @throws DomesticRateException If the API response contains any error
	 */
	public function getResult()
	{
		$xmldoc = parent::getResult();
		if(!is_a($xmldoc, 'SimpleXMLElement')) {
			throw new BadResponseException("Response from DomesticRate API was not a SimpleXMLElement");
		}
		if(strtolower($xmldoc->getName()) == 'error') {
			throw new DomesticRateException($xmldoc);
		}
		
		$errors = $xmldoc->xpath('//Package/Error');
		if(count($errors) && $this->client->getPackageExceptions()) {
			throw new DomesticRateException($errors[0]);
		}
		
		return $xmldoc;
	}
	
	/**
	 * Assumes that the response type is xml without validating it, will throw exceptions accordingly
	 * @see Guzzle\Service\Command.AbstractCommand::process()
	 */
	protected function process() {
		$this->result = $this->getRequest()->getResponse();
		$this->result = new \SimpleXMLElement(trim($this->result->getBody(true)));
	}
}