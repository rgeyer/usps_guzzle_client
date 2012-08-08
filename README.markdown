Guzzle USPS Rate API client for PHP
==========================================

usps_guzzle_client is a PHP Guzzle REST API client library for the USPS Rate API.

## Installation

### Production
Add usps_guzzle_client to the src/Guzzle/Usps directory of your Guzzle
installation:

    cd /path/to/guzzle
    git submodule add git://github.com/rgeyer/usps_guzzle_client.git ./src/Guzzle/Usps

You can now build a phar file containing guzzle-aws and the main guzzle framework:

    cd /path/to/guzzle/build
    phing phar

Now you just need to include guzzle.phar in your script.  The phar file
will take care of autoloading Guzzle classes:

```php

    <?php
    require_once 'guzzle.phar';
```

The example script for instantiating the USPS Rate client and getting a simple domestic rate quote:
```php
<?php
    require_once 'guzzle.phar';
    $serviceBuilder = \Guzzle\Service\ServiceBuilder::factory(array(
    'guzzle-usps' => array(
        'class'     => 'Guzzle\Usps\UspsClient',
        'params'     => array(
            'username' => 'YourUSPSAssignedUsername'
        )
    ),
    ));

    $client = $serviceBuilder->get('guzzle-usps');

    $packages = array(
      array(
        'Service' => 'ALL',
        'ZipOrigination' => '93117',
        'ZipDestination' => '93101',
        'Pounds' => 0,
        'Ounces' => 1,
        'Container' => 'VARIABLE',
        'Size' => 'REGULAR',
        'Machinable' => 'True'        
      )
    );

    $params = array('packages' => $packages);
    $command = $client->getCommand('domestic_rate', $params);

```

### Development
Follow the install steps for installing guzzle using composer, documented here.

https://github.com/guzzle/guzzle/blob/v2.8.3/README.md

Then, copy phpunit.xml.dst to phpunit.xml.

Fill out the fields in phpunit.xml.

Run the tests
```
phpunit -c phpunit.xml
```

## Usage

### Optional Settings
When instatiating the UspsClient, you can specify some optional settings which change the behavior of the client.

The first is the 'package_exception' setting.  When making calls to the USPS Rate API if the response to your request is an error, a Guzzle\Usps\Common\DomesticRateException or Guzzle\Usps\Common\IntlRateException is thrown.

However when requesing rates for many packages it is possible that the request will be successful, but individual packages might have errors due to the request parameters you choose.  By default, the UspsClient will throw an exception on the first package error it encounters.  If however you'd like to inspect each "Package" in the response for errors, and continue processing once you've handled the error in the response, you can set 'package_exceptions' to false when instantiating the UspsClient.

An example of instantiating the client with 'package_exceptions' set to false.

```php
<?php
    require_once 'guzzle.phar';
    $serviceBuilder = \Guzzle\Service\ServiceBuilder::factory(array(
    'guzzle-usps' => array(
        'class'     => 'Guzzle\Usps\UspsClient',
        'params'     => array(
            'username' => 'YourUSPSAssignedUsername',
            'package_exceptions' => false
        )
    ),
    ));

    $client = $serviceBuilder->get('guzzle-usps');
```

You can also use an accessor for this setting 
```
# Store the old setting
$old_value = $client->getPackageExceptions();

# Disable package exceptions for a single requests
$client->setPackageExceptions(false);

# Any requests made here with package specific errors won't have exceptions thrown

# Restore the previous setting
$client->setPackageExceptions($old_value);
```

Also, here is an example output with two packages, one with an error, and one without when package_exceptions is set to false

*Request PHP Code*
```
    $packages = array(
      array(
        'Service' => 'ALL',
        'ZipOrigination' => '93117',
        'ZipDestination' => '93101',
        'Pounds' => 0,
        'Ounces' => 1,
        'Container' => 'VARIABLE',
        'Size' => 'REGULAR'
      ), # This lacks "Machinable" and will include an error in the response
      array(
        'Service' => 'PARCEL',
        'ZipOrigination' => '93117',
        'ZipDestination' => '93101',
        'Pounds' => 0,
        'Ounces' => 1,
        'Container' => 'VARIABLE',
        'Size' => 'REGULAR',
        'Machinable' => 'True'        
      )
    );
    
    $params = array('packages' => $packages);
    $command = $client->getCommand('domestic_rate', $params);
    $command->execute();
    $result = $command->getResult();
    
    print_r($result);    
```

*Output*
```
SimpleXMLElement Object
(
    [Package] => Array
        (
            [0] => SimpleXMLElement Object
                (
                    [@attributes] => Array
                        (
                            [ID] => 0
                        )

                    [Error] => SimpleXMLElement Object
                        (
                            [Number] => -2147219487
                            [Source] => DomesticRatesV4;clsRateV4.ValidateMachinable;RateEngineV4.ProcessRequest
                            [Description] => Machinable value must be 'True' or 'False' for service type Parcel Post and service type All.
                            [HelpFile] => SimpleXMLElement Object
                                (
                                )

                            [HelpContext] => 1000440
                        )

                )

            [1] => SimpleXMLElement Object
                (
                    [@attributes] => Array
                        (
                            [ID] => 1
                        )

                    [ZipOrigination] => 93117
                    [ZipDestination] => 93101
                    [Pounds] => 0
                    [Ounces] => 1
                    [Container] => VARIABLE
                    [Size] => REGULAR
                    [Machinable] => TRUE
                    [Zone] => 1
                    [Postage] => SimpleXMLElement Object
                        (
                            [@attributes] => Array
                                (
                                    [CLASSID] => 4
                                )

                            [MailService] => Parcel Post&lt;sup&gt;&amp;reg;&lt;/sup&gt;
                            [Rate] => 5.20
                            [SpecialServices] => SimpleXMLElement Object
                                (
                                    [SpecialService] => Array
                                        (
                                            [0] => SimpleXMLElement Object
                                                (
                                                    [ServiceID] => 9
                                                    [ServiceName] => Certificate of Mailing
                                                    [Available] => true
                                                    [AvailableOnline] => false
                                                    [Price] => 1.15
                                                    [PriceOnline] => 0
                                                )

                                            [1] => SimpleXMLElement Object
                                                (
                                                    [ServiceID] => 1
                                                    [ServiceName] => Insurance
                                                    [Available] => true
                                                    [AvailableOnline] => false
                                                    [Price] => 1.85
                                                    [PriceOnline] => 0
                                                    [DeclaredValueRequired] => true
                                                    [DueSenderRequired] => false
                                                )

                                            [2] => SimpleXMLElement Object
                                                (
                                                    [ServiceID] => 13
                                                    [ServiceName] => Delivery Confirmation&lt;sup&gt;&amp;trade;&lt;/sup&gt;
                                                    [Available] => true
                                                    [AvailableOnline] => true
                                                    [Price] => 0.85
                                                    [PriceOnline] => 0.19
                                                )

                                            [3] => SimpleXMLElement Object
                                                (
                                                    [ServiceID] => 7
                                                    [ServiceName] => Return Receipt for Merchandise
                                                    [Available] => true
                                                    [AvailableOnline] => false
                                                    [Price] => 3.95
                                                    [PriceOnline] => 0
                                                )

                                            [4] => SimpleXMLElement Object
                                                (
                                                    [ServiceID] => 15
                                                    [ServiceName] => Signature Confirmation&lt;sup&gt;&amp;trade;&lt;/sup&gt;
                                                    [Available] => true
                                                    [AvailableOnline] => true
                                                    [Price] => 2.55
                                                    [PriceOnline] => 2.10
                                                )

                                            [5] => SimpleXMLElement Object
                                                (
                                                    [ServiceID] => 6
                                                    [ServiceName] => Collect on Delivery
                                                    [Available] => true
                                                    [AvailableOnline] => false
                                                    [Price] => 5.90
                                                    [PriceOnline] => 0
                                                    [DeclaredValueRequired] => true
                                                    [DueSenderRequired] => true
                                                )

                                        )

                                )

                        )

                )

        )

)
```