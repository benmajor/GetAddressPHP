# GetAddress.io PHP SDK

This is a PHP SDK wrapper for the excellent GetAddress.io API for performing UK postcode lookups at incredibly realistic and affordable prices for developers and store owners. Official SDKs are available for .NET and jQuery, and while there are several written in PHP available, most seem to be incomplete when compared to the current API documentation available on the getAddress() website.

Please note, this library is not maintained by GetAddress.io or its staff. All efforts by the plugin author have been made to ensure that the SDK is up-to-date with the current API release as outline in the [online documentation](https://getaddress.io/Documentation).

[TOC]

### 1. Installation:

The easiest and most convenient way to install the SDK is to use [Composer](https://getcomposer.org/) as follows:

```bash
$ composer install benmajor/getaddress-php
```

In addition to installing the SDK via Composer, you will also need to obtain an API key for use with it. This can be done via the GetAddress.io website. [Get your API key](https://getaddress.io/).

### 2. Usage:

The SDK provides utility methods for the various endpoints exposed by the GetAddress.io API. The following usage document explains how these can be used within an application. 

**Looking up a postcode:**

```php
<?php

use BenMajor\GetAddressPHP\Client;

$client = new Client('YOUR_API_KEY');
$addresses = $client->lookup('SW1A 2AA');
```

The above snippet will lookup the postcode `SW1A 2AA`, and returns an `IterableResposne` object that can be used to loop over the returned addresses. `IterableResponse` objects can be used in various ways, and expose a variety of methods used to interact with the collection. For example, given the above snippet we can call various methods to interrogate the response, as follows:

```php
<?php
    
$numResults = $addresses->count(); # returns the number of addresses
$first = $addresses->reset();      # get the first address in the response
$last = $address->last();		  # get the last address in the response
$next = $address->next();		  # get the next address in the loop
$prev = $address->prev();          # get the previous address in the loop
$current = $address->current();    # get the address at the current pointer position

# Loop over all of the addresses in the response ($address is now an Address object)
# The lambda function will be called on each element:
$address->each(function($address) {  });
```

### 3. Method Reference:

`setAPIKey( $key )`
Returns: `Client`
Set the API key to be used with the response.

`setAdminKey( $key )`
Returns: `Client`
Set the admin key to be used with requests made to the admin endpoints.

`lookup( $postcode, $property = null )`
Returns: `IterableResponse` of `Address` entities.
Looks up a specific postcode with an optional property name or number.

`suggest( $term, $numResults = 6, $filters = [ ] )`
Returns `IterableResponse` of `Suggestion` entities.
Performs an autocomplete lookup based on the search term specified in the first parameter. The number of results should be passed into the second argument (and defaults to 6), and filters (to restrict the results) should be passed as an associative array of any or all of the following keys:

```php
[
    'county' => '{county}',
    'locality' => '{locality}',
    'district' => '{district_name}',
    'town_or_city' => '{town_name}',
    'postcode' => '{postcode}',
    'residential' => '{true|false}',
    'radius' => [
        'km' => '{max distance from lat/long in kilometres}',
        'longitude' => '{longitude}',
        'latitude': '{latitude}'
    ]
]
```

Please see the [GetAddress Documentation](https://getaddress.io/Documentation) for more information about filtering autosuggestion results.

`typeahead( $term, $results = 6, $search = [ ], $filter = [ ] )`
Returns: `Array` 
The Typeahead API helps users complete forms and issue better search queries by completing partial search terms. The first parameter used is the term with which to search. The second parameter allows us to limit the number of results. `$search` should be passed to the third parameter as an array, and specifies the fields of an address that should be used for the search, containing any of the following values:

`postcode`, `line_1`, `line_2`, `line_3`, `locality`, `town_or_city`, `district`, `county`, `country`

`$filter` should be passed in to the last parameter as an associative array (see example in `suggest()` documentation above for more information).

`get( $id )`
Returns: `Address` entity
Retrieve a specific address by ID.

`distance( $postcodeFrom, $postcodeTo )`
Returns: `DistanceResult` entity
Calculate the distance between two postcodes.

`usage( $from = null, $to = null )`
Returns: `Response` object
Gets the current account usage, optionally filtered from a specific date (and to a specific date). When specified, both `$from` and `$to` should be PHP `DateTime` objects. Returns an object containing the following parameters:

```json
{
    "usage_today": 99,
    "daily_limit": 2000,
    "monthly_buffer": 1000,
    "monthly_buffer_used": 100
}
```

`subscription()`
Returns: `SubscriptionInfo` object
Gets the details of the current subscription based on the used API key.



### 4. Entity Reference:



