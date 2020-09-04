<?php

namespace BenMajor\GetAddressPHP;

use GuzzleHttp\Client as Guzzle;

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

		$endpoint = 'distance/'.$cleanFrom.'/'.$cleanTo;

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
	public function addDomainToWhitelist( string $domain )
	{
		$response = $this->sendRequest(
			'security/domain-whitelist',
			[ ],
			true,
			'post',
			[ 'name' => $domain ]
		);

		return $response->getId();
	}

	# Removes a domain from your white list.
	# DELETE: /security/domain-whitelist/{id}
	public function removeDomainFromWhitelist( Entity\Domain $domain )
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

		return new Entity\Domain($result);
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

		$return = new IterableResponse([ ]);

		foreach( $response as $domain )
		{
			$return->addItem(
				new Entity\Domain($domain)
			);
		}

		return $return;
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