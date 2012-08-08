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

namespace Guzzle\Usps\Tests\Command;

class DomesticRateTest extends \Guzzle\Tests\GuzzleTestCase {
	
	protected $_client;
	
	protected function setUp() {
		parent::setUp();
		
		$this->_client = $this->getServiceBuilder()->get('test.guzzle-usps');
	}
	
	private function executeCommand($commandName, array $params = array(), &$command = null) {
		$command = $this->_client->getCommand($commandName, $params);
		$command->execute();
		$result = $command->getResult();
				
		return $result;
	}
	
	public function testCanGetDomesticRateForOnePackage() {
		$packages = array(
			array(
				'ID' => 'Identifiable',
        'ZipOrigination' => '93117',
        'ZipDestination' => '93101',
        'Pounds' => 0,
        'Ounces' => 1,
        'Container' => 'VARIABLE',
        'Service' => 'ALL',
        'Size' => 'REGULAR',
        'Machinable' => 'True'				
			)
		);
		
		$command = null;
		$result = $this->executeCommand('domestic_rate', array('packages' => $packages), $command);
		
		$this->assertEquals(1, count($result));
		$attributes = $result[0]->Package->attributes();
		$this->assertEquals(1, count($attributes));
		$this->assertEquals('Identifiable', $attributes['ID']);
	}
	
	public function testCanGetDomesticRateForManyPackages() {
		$packages = array(
			array(
        'ZipOrigination' => '93117',
        'ZipDestination' => '93101',
        'Pounds' => 0,
        'Ounces' => 1,
        'Container' => 'VARIABLE',
        'Service' => 'ALL',
        'Size' => 'REGULAR',
        'Machinable' => 'True'				
			),
			array(
        'ZipOrigination' => '93117',
        'ZipDestination' => '93101',
        'Pounds' => 0,
        'Ounces' => 2,
        'Container' => 'VARIABLE',
        'Service' => 'ALL',
        'Size' => 'REGULAR',
        'Machinable' => 'True'				
			)
		);
		
		$command = null;
		$result = $this->executeCommand('domestic_rate', array('packages' => $packages), $command);

		$this->assertEquals(2, count($result));
		$idx = 0;
		foreach($result as $package) {
			$attributes = $package->attributes();
			$this->assertEquals(strval($idx), $attributes['ID']);			
			# Hacky, but we're going to increment here, then do a check that depends upon a non 0 index
			$idx++;
			$this->assertEquals(strval($idx), $package->Ounces);
		}
	}
	
	/**
	 * @expectedException Guzzle\Usps\Common\DomesticRateException
	 * @expectedExceptionMessage Machinable value must be 'True' or 'False' for service type Parcel Post and service type All.
	 */
	public function testThrowsDomesticRateExceptionForPackagesWhenPackageExceptionsIsEnabled() {
		$packages = array(
			array(
        'ZipOrigination' => '93117',
        'ZipDestination' => '93101',
        'Pounds' => 0,
        'Ounces' => 2,
        'Container' => 'VARIABLE',
        'Service' => 'ALL',
        'Size' => 'REGULAR'				
			)
		);
		
		$command = null;
		$result = $this->executeCommand('domestic_rate', array('packages' => $packages), $command);
	}
	
	public function testReturnsPackageErrorsWhenPackageExceptionsIsDisabled() {
		$this->assertEquals(true, $this->_client->getPackageExceptions());
		$this->_client->setPackageExceptions(false);
		$this->assertEquals(false, $this->_client->getPackageExceptions());
		
		$packages = array(
			array(
        'ZipOrigination' => '93117',
        'ZipDestination' => '93101',
        'Pounds' => 0,
        'Ounces' => 2,
        'Container' => 'VARIABLE',
        'Service' => 'ALL',
        'Size' => 'REGULAR'				
			)
		);
		
		$command = null;
		$result = $this->executeCommand('domestic_rate', array('packages' => $packages), $command);
		
		$errors = $result->xpath('//Package/Error');
		$this->assertEquals(1, count($errors));
		$this->assertEquals("Machinable value must be 'True' or 'False' for service type Parcel Post and service type All.", $errors[0]->Description);
		
		$this->_client->setPackageExceptions(true);
		$this->assertEquals(true, $this->_client->getPackageExceptions());		
	}
	
	/**
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionMessage You must specify at least one package
	 */
	public function testThrowsArgumentExceptionWhenNoPackagesAreSpecified() {
		$command = null;
		$result = $this->executeCommand('domestic_rate', array('packages' => array()), $command);		
	}
	
	/**
	 * @expectedException Guzzle\Usps\Common\DomesticRateException
	 * @expectedExceptionMessage Invalid XML Element content is incomplete according to the DTD/Schema.
 line= 0 pos= 1715
	 */
	public function testErrorsAreConvertedToExceptions() {
		$packages = array(
			array(
				'ID' => 'whoa',
				'Service' => 'FOOBARBAZ'				
			)
		);
		$this->executeCommand('domestic_rate', array('packages' => $packages));
	}
	
}