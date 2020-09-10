<?php

namespace BenMajor\GetAddressPHP;

use GuzzleHttp\Client as Guzzle;
use BenMajor\GetAddressPHP\Exception\EmailException;

class Client
{
	const BASE_URL = 'https://api.getAddress.io';

	protected $key;
	protected $adminKey;

	private $client;

	function __construct( string $key, bool $relaxedSSL = false )
	{
		$this->key = $key;

		$this->client = new Guzzle([ 
			'base_uri' => self::BASE_URL,
			'verify' => (!$relaxedSSL)
		]);
	}

	# Set the API key:
	public function setAPIKey( string $key )
	{
		$this->key = $key;

		# Return self to preserve method-chaining:
		return $this;
	}

	# Set the admin key:
	public function setAdminKey( string $key )
	{
		$this->adminKey = $key;

		# Return self to preserve method-chaining:
		return $this;
	}

	# Postal addresses for single UK postcode.
	# GET: /find/{postcode}  and /find/{postcode}/{house}
	public function lookup( string $postcode, string $property = null )
	{
		$endpoint = 'find/';
		$endpoint.= $postcode;

		if( !is_null($property) && !empty($property) )
		{
			$endpoint.= '/'.$property;
		}

		return $this->sendRequest($endpoint, [ 
			'expand' => 'true',
			'sort' => 'true'
		]);
	}

	# The Suggest API lists partial address results for a given term.
	# GET: /suggest/{term}
	public function suggest( string $term, int $results = 6, array $filters = [ ] )
	{
		$endpoint = 'suggest/';
		$endpoint.= trim($term);

		if( $endpoint == 'suggest/' )
		{
			throw new Exception\LookupException('Query term for autosuggest cannot be empty.');
		}

		$requestData = [
			'top' => $results
		];

		# Are we filtering?
		if( ! empty($filters) )
		{
			$requestData['filter'] = $filters;
		}

		return $this->sendRequest($endpoint, $requestData);
	}

	# The Typeahead API helps users complete forms and issue better search queries by completing partial search terms.
	# GET: /typeahead/{term}
	public function typeahead( string $term, int $results = 6, array $search = [ ], array $filters = [ ] )
	{
		$endpoint = sprintf('/typeahead/%s', urlencode($term));

		if( $results < 0 )
		{
			throw new \Exception('Number of results must be a positive integer.');
		}

		$requestData = [ 'top' => $results ];

		if( ! empty($search) )
		{
			$requestData['search'] = $search;
		}

		if( ! empty($filters) )
		{
			$requestData['filters'] = $filters;
		}

		# Send off the request:
		$response = $this->sendRequest(
			$endpoint,
			[ ],
			false,
			'get',
			$requestData,
			true
		);

		return $response;
	}

	# Get a specific address by ID (usually returned by a suggestion).
	# GET: /get/{id}
	public function get( string $id )
	{
		$clean = trim($id);

		if( empty($clean) )
		{
			throw new Exception\LookupException('Address ID cannot be empty.');
		}

		$result = $this->sendRequest('get/'.$id);

		return new Entity\Address(
			$result->getParsedBody()
		);
	}

	# Gets the distance in meters between two postcodes
	# GET: /distance/{postcode_from}/{postcode_to} 
	public function distance( string $postcodeFrom, string $postcodeTo )
	{
		$cleanFrom = trim($postcodeFrom);
		$cleanTo = trim($postcodeTo);

		if( empty($cleanFrom) )
		{
			throw new Exception\LookupException('Source postcode cannot be empty.');
		}

		if( empty($cleanTo) )
		{
			throw new Exception\LookupException('Destination postcode cannot be empty.');
		}

		$endpoint = sprintf('distance/%s/%s', $cleanFrom, $cleanTo);

		return new Entity\DistanceResult($this->sendRequest($endpoint, [ ], false, 'get', null, true));
	}

	# Get the current usage.
	# GET: /usage
	public function usage( \DateTime $from = null, \DateTime $to = null )
	{
		$endpoint = [ 'v3', 'usage' ];

		if( !is_null($from) && !is_null($to) )
		{
			array_push($endpoint,
				'from',
				$from->format('d'),
				$from->format('m'),
				$from->format('Y'),
				'To',
				$to->format('d'),
				$to->format('m'),
				$to->format('Y')
			);
		}
		elseif( !is_null($from) )
		{
			array_push($endpoint,
				$from->format('d'),
				$from->format('m'),
				$from->format('Y')
			);
		}

		return $this->sendRequest(
			implode('/', $endpoint),
			[ ],
			true
		);
	}

	# Your Subscription Details
	# GET: /subscription 
	public function subscription()
	{
		return $this->sendRequest(
			'subscription',
			[ ],
			true
		);
	}

	# Add a new address
	# POST: /private-address/{postcode}
	public function addPrivateAddress( Entity\PrivateAddress $address )
	{
		try
		{
			$response = $this->sendRequest(
				'private-address/'.$address->getPostcode(),
				[ ],
				true,
				'post',
				$address->toRequestObject()
			);

			return true;
		}
		catch( \Exception $e )
		{
			return false;
		}
	}

	# Retrieve a single private address by ID:
	# GET: /private-address/{postcode}/{id}
	public function getPrivateAddress( string $postcode, int $id )
	{
		$response = $this->sendRequest(
			'private-address/'.$postcode.'/'.$id,
			[ ],
			true,
			'get',
			null,
			true
		);

		return new Entity\PrivateAddress($postcode, $response);
	}

	# Get a list of private addresses for a postcode:
	# GET: /private-address/{postcode}
	public function getPrivateAddresses( string $postcode )
	{
		$response = $this->sendRequest(
			'private-address/'.$postcode,
			[ ],
			true,
			'get',
			null,
			true
		);

		$return = new IterableResponse([]);

		foreach( $response as $addressRaw )
		{
			$return->addItem(
				new Entity\PrivateAddress($postcode, $addressRaw)
			);
		}

		return $return;
	}

	# Delete a private address from a postcode by ID:
	# DELETE: /private-address/{postcode}/{id}
	public function deletePrivateAddress( string $postcode, int $id )
	{
		return $this->sendRequest(
			'private-address/'.$postcode.'/'.$id,
			[ ],
			true,
			'delete'
		);
	}

	# Gets the account's current API key.
	# GET: /security/api-key 
	public function getAPIKey()
	{
		$response = $this->sendRequest(
			'security/api-key',
			[ ],
			true
		);

		return $response->get('api-key');
	}

	# Updates the account's current API key with a new API key.
	# PUT: /security/api-key 
	public function refreshAPIKey()
	{
		$response = $this->sendRequest(
			'security/api-key',
			[ ],
			true,
			'put'
		);

		return $response->get('api-key');
	}

	# Adds domain to white list.
	# POST: /security/domain-whitelist 
	public function addWhitelistDomain( string $domain )
	{
		$response = $this->sendRequest(
			'/security/domain-whitelist',
			[ ],
			true,
			'post',
			[ 'name' => $domain->getName() ],
			true
		);

		return new Entity\Domain(
			$domain,
			$response->id
		);
	}

	# Removes a domain from your white list.
	# DELETE: /security/domain-whitelist/{id}
	public function removeWhitelistDomain( Entity\Domain $domain )
	{
		$endpoint = 'security/domain-whitelist/'.$domain->getId();

		return $this->sendRequest(
			$endpoint,
			[ ],
			true,
			'delete'
		);
	}

	# Get a domain from your white list.
	# GET: /security/domain-whitelist/{id}
	public function getWhitelistDomain( $id )
	{
		$endpoint = 'security/domain-whitelist/'.$id;

		$result = $this->sendRequest($endpoint, [ ], true, 'get', null, true);

		return new Entity\Domain(
			$result->name, 
			$result->id
		);
	}

	# Lists all domains in your white list.
	# GET: /security/domain-whitelist  
	public function getWhitelistDomains()
	{
		$response = $this->sendRequest(
			'/security/domain-whitelist',
			[ ],
			true,
			'get',
			null,
			true
		);

		$return = new IterableResponse();

		foreach( $response as $domain )
		{
			$return->addItem(
				new Entity\Domain($domain->name, $domain->id)
			);
		}

		return $return;
	}

	# Adds an IP address to your white list.
	# POST: /security/ip-address-whitelist 
	public function addIPAddress( string $address )
	{
		$response = $this->sendRequest(
			'/security/ip-address-whitelist',
			[ 'value' => $address->getValue() ],
			true,
			'post',
			[ ],
			true
		);

		return new Entity\IPAddress(
			$address,
			$response->id
		);
	}

	# Removes an IP address from your white list.
	# DELETE: /security/ip-address-whitelist/{id} 
	public function removeIPAddress( Entity\IPAddress $address )
	{
		$endpoint = '/security/ip-address-whitelist/'.$address->getId();

		return $this->sendRequest(
			$endpoint,
			[ ],
			true,
			'delete'
		);
	}

	# Get an IP address from your white list.
	# GET: /security/ip-address-whitelist/{id}
	public function getIPAddress( $id )
	{
		$endpoint = '/security/ip-address-whitelist/'.$id;

		$response = $this->sendRequest(
			$endpoint,
			[ ],
			true,
			'get',
			[ ],
			true
		);

		return new Entity\IPAddress(
			$response->value,
			$response->id
		);
	}

	# Lists all IP addresses in your white list.
	# GET: /security/ip-address-whitelist
	public function getIPAddresses()
	{
		$response = $this->sendRequest(
			'/security/ip-address-whitelist',
			[ ],
			true,
			'get',
			[ ],
			true
		);

		$return = new IterableResponse();

		foreach( $response as $address )
		{
			$return->addItem(
				new Entity\IPAddress($address->value, $address->id)
			);
		}

		return $return;
	}

	# Gets additional user's permissions.
	# GET: /permission/{email-address}/
	public function getPermissions( string $email )
	{
		if( ! filter_var($email, FILTER_VALIDATE_EMAIL) )
		{
			throw new EmailException('Specified email address is invalid.');
		}

		$response = $this->sendRequest(
			'/permission/'.$email.'/',
			[ ],
			true,
			'get',
			[ ],
			true
		);

		return new Entity\Permissions($response);
	}

	# Lists all permissions.
	# GET: /permission
	public function getAllPermissions()
	{
		$response = $this->sendRequest(
			'/permission',
			[ ],
			true,
			'get',
			[ ],
			true
		);

		$return = new IterableResponse();

		foreach( $response as $perms )
		{
			$return->add(
				new Entity\Permissions($perms)
			);
		}

		return $return;
	}

	# Create permissions for an additional user.
	# POST: /permission 
	public function addPermissions( Entity\Permissions $permissions )
	{
		return $this->sendRequest(
			'/permission',
			[ ],
			true,
			'post',
			$permissions->toJSON()
		);
	}	

	# Removes all permissions for an additional user.
	# DELETE: /permission/{email-address}/
	public function removePermissions( Entity\Permissons $permissions )
	{
		return $this->sendRequest(
			'/permission/'.$permission->getEmailAddress(),
			[ ],
			true,
			'delete'
		);
	}

	# Updates additional user's permissions.
	# PUT: /permission 
	public function updatePermissions( Entity\Permissions $permissions )
	{
		return $this->sendRequest(
			'/permssion',
			[ ],
			true,
			'put',
			$permissions->toJSON()
		);
	}

	# Gets the account's primary email address.
	# GET: /email-address
	public function getPrimaryEmailAddress()
	{
		$response = $this->sendRequest(
			'/email-address',
			[ ],
			true,
			'get', 
			null,
			true
		);

		return $response->{'email-address'};
	}

	# Updates the account's primary email address with a new email address.
	# PUT: /email-address
	public function setPrimaryEmailAddress( string $email )
	{
		if( ! filter_var($email, FILTER_VALIDATE_EMAIL) )
		{
			throw new EmailException('The specified email address is invalid.');
		}

		return $this->sendRequest(
			'/email-address',
			[ ],
			true,
			'put',
			[ 'new-email-address' => $email ]
		);
	}

	# Gets an invoice
	# GET: /invoices/{number}
	public function getInvoice( string $number )
	{
		$endpoint = sprintf('/invoices/%s', $number);

		$response = $this->sendRequest(
			$endpoint,
			[ ],
			true,
			'get',
			null,
			true
		);

		return new Entity\Invoice($response);
	}

	# Returns the invoices for a given date range
	# GET: /invoices/from/{from-day}/{from-month}/{from-year}/To/{to-day}/{to-month}/{to-year}
	public function getInvoices( \DateTime $from, \DateTime $to )
	{
		$endpoint = sprintf(
			'/invoices/from/%s/%s/%s/To/%s/%s/%s',
			$from->format('d'),
			$from->format('m'),
			$from->format('Y'),
			$to->format('d'),
			$to->format('m'),
			$to->format('Y')
		);

		$response = $this->sendRequest(
			$endpoint,
			[ ],
			true,
			'get',
			null,
			true
		);

		$response = new IterableResponse();

		foreach( $response as $inv )
		{
			$response->add(
				new Entity\Invoice($inv)
			);
		}

		return $response;
	}

	# Lists all invoices
	# GET: /invoices 
	public function listInvoices()
	{
		$response = $this->sendRequest(
			'/invoices',
			[ ],
			true,
			'get',
			null,
			true
		);

		$response = new IterableResponse();

		foreach( $response as $inv )
		{
			$response->add(
				new Entity\Invoice($inv)
			);
		}

		return $response;
	}

	# Adds copied recipient to your emailed invoices
	# POST: /cc/invoices 
	public function addCCEmailAddress( string $email )
	{
		if( ! filter_var($email, FILTER_VALIDATE_EMAIL) )
		{
			throw new EmailException('Specified email address is invalid.');
		}

		$response = $this->sendRequest(
			'/cc/invoices',
			[ 'email-address' => $email ],
			true,
			'post',
			null,
			true
		);

		return new Entity\EmailAddress(
			$email,
			$response->id
		);
	}

	# Removes a copied recipient.
	# DELETE: /cc/invoices/{id}
	public function deleteCCEmailAddress( Entity\EmailAddress $email )
	{
		$endpoint = sprintf('/cc/invoices/%s', $email->getId());

		return $this->sendRequest(
			$endpoint,
			[ ],
			true,
			'delete'
		);
	}

	# Gets copied recipient.
	# GET: /cc/invoices/{id}
	public function getCCEmailAddress( $id )
	{
		$endpoint = sprintf('/cc/invoices/%s', $id);

		$response = $this->sendRequest(
			$endpoint,
			[ ],
			true,
			'get',
			null,
			true
		);

		return new Entity\EmailAddress(
			$response->{'email-address'},
			$response->id
		);
	}

	# Lists all copied recipients.
	# GET: /cc/invoices
	public function getCCEmailAddresses()
	{
		$response = $this->sendRequest(
			'/cc/invoices',
			[ ],
			true,
			'get',
			null,
			true
		);

		$return = new IterableResponse();

		foreach( $response as $email )
		{
			$return->add(
				new Entity\EmailAddress(
					$email->{'email-address'},
					$email->id
				)
			);
		}

		return $return;
	}

	# Adds copied recipient to your expired account emails
	# POST: /cc/expired 
	public function addCCExpiredEmailAddress( string $email )
	{
		if( ! filter_var($email, FILTER_VALIDATE_EMAIL) )
		{
			throw new EmailException('Specified email address is invalid.');
		}

		$response = $this->sendRequest(
			'/cc/invoices',
			[ 'email-address' => $email ],
			true,
			'post',
			null,
			true
		);

		return new Entity\EmailAddress(
			$email,
			$response->id
		);
	}

	# Removes a copied recipient.
	# DELETE: /cc/expired/{id}
	public function deleteCCExpiredEmailAddress( Entity\EmailAddress $email )
	{
		$endpoint = sprintf('/cc/expired/%s', $email->getId());

		return $this->sendRequest(
			$endpoint,
			[ ],
			true,
			'delete'
		);
	}

	# Gets copied recipient.
	# GET: /cc/expired/{id}
	public function getCCExpiredEmailAddress( $id )
	{
		$endpoint = sprintf('/cc/expired/%s', $id);

		$response = $this->sendRequest(
			$endpoint,
			[ ],
			true,
			'get',
			null,
			true
		);

		return new Entity\EmailAddress(
			$response->{'email-address'},
			$response->id
		);
	}

	# Lists all copied recipients.
	# GET: /cc/expired 
	public function getCCExpiredEmailAddresses()
	{
		$response = $this->sendRequest(
			'/cc/expired',
			[ ],
			true,
			'get',
			null,
			true
		);

		$return = new IterableResponse();

		foreach( $response as $email )
		{
			$return->add(
				new Entity\EmailAddress(
					$email->{'email-address'},
					$email->id
				)
			);
		}

		return $return;
	}

	# Returns the subscription's payment terms, limits and expiry date.
	# GET: /subscription 
	public function getSubscriptionInfo()
	{
		$response = $this->sendRequest(
			'subscription',
			[ ],
			true,
			'get',
			null,
			true
		);

		return new Entity\SubscriptionInfo($response);
	}

	private function sendRequest( string $endpoint, array $data = [ ], bool $adminEndpoint = false, string $method = 'get', $body = null, bool $returnRaw = false ) 
	{
		$url = self::BASE_URL.'/'.trim($endpoint, '/');

		try
		{
			$keyToUse = ($adminEndpoint) ? $this->adminKey : $this->key;

			$requestData = [
				'query' => array_merge([ 'api-key' => $keyToUse], $data)
			];

			if( ! is_null($body) )
			{
				if( is_scalar($body) )
				{
					$requestData['body'] = $body;
				}
				else
				{
					$requestData['body'] = json_encode($body);
					$requestData['headers'] = [ 'Content-type' => 'application/json' ];
				}
			}

			$request = $this->client->request(strtoupper($method), $url, $requestData);

			try
			{
				$response = json_decode($request->getBody());

				if( $returnRaw === true )
				{
					return $response;
				}

				# If it's an array, return a new IterableResponse
				if( is_array($response) )
				{
					return new IterableResponse( $response );
				}
				
				return new Response($response);
			}
			catch( \Exception $e )
			{
				throw new Exception\DecodeException('Error decoding getaddress.io response.');
			}
		}
		catch( \Exception $e )
		{
			if( $e->getResponse()->getStatusCode() == 401 )
			{
				throw new Exception\AuthenticationException('getaddress.io authentication failed.');
			}

			throw new Exception\LookupException($e->getMessage());
		}
	}
}