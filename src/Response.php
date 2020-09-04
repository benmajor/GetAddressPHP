<?php

namespace BenMajor\GetAddressPHP;

class Response
{
	private $raw;

	function __construct( $responseBody )
	{
		$this->raw = $responseBody;
	}

	# Get the raw body:
	public function getBody()
	{
		return json_encode($this->raw);
	}

	# Get the body of the response as an object:
	public function getParsedBody()
	{
		return $this->raw;
	}

	# Get the addresses:
	public function getAddresses() : array
	{
		# No addresses on response, return an empty array:
		if( !isset($this->raw->addresses) || !is_array($this->raw->addresses) )
		{
			return [ ];
		}

		$addresses = [ ];

		foreach( $this->raw->addresses as $address )
		{
			$addresses[] = new Entity\Address($address, $this->raw->postcode);
		} 

		return $addresses;
	}

	# Get the suggestions:
	public function getSuggestions() : array
	{
		# No suggestions on response, return an empty array:
		if( !isset($this->raw->suggestions) || !is_array($this->raw->suggestions) )
		{
			return [ ];
		}

		$suggestions = [ ];

		foreach( $this->raw->suggestions as $suggestion )
		{
			$suggestions[] = new Entity\Suggestion($suggestion, Client::BASE_URL);
		}

		return $suggestions;
	}

	# Get a property by string:
	public function get( $property )
	{
		return $this->raw->{$property};
	}

	function __call( $method, $args )
	{
		if( substr($method, 0, 3) == 'get' )
		{
			$property = lcfirst(substr($method, 3));
			
			if( ! isset($this->raw->{$property}) )
			{
				throw new Exception\ResponseException(
					sprintf('Property %s does not exist in response.', $property)
				);
			}

			return $this->raw->{$property};
		}
	}

	function __toString()
	{
		return $this->getBody();
	}
}