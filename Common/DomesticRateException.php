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

namespace Guzzle\Usps\Common;

/**
 * An exception class for DomesticRate requests
 * 
 * @author Ryan J. Geyer <me@ryangeyer.com>
 *
 */
use Guzzle\Http\Message\Response;

class DomesticRateException extends \Exception {
	
	/**
	 * The error source as defined in the <Source> node of the XML response from the
	 * DomesticRate API call
	 * @var string
	 */
	protected $source;

	/**
	 * The SimpleXMLElement response from the DomesticRate API which contains an error
	 * 
	 * @param SimpleXMLElement $xmldoc
	 */
	public function __construct($xmldoc) {
		$this->code = $xmldoc->Number;
		$this->message = $xmldoc->Description;
		$this->source = $xmldoc->Source;
	}

	/**
	 * @return string The error source as defined in the <Source> node of the XML Response
	 * from the DomesticRate API call
	 */
	public function getSource() {
		return $this->source;
	}
}