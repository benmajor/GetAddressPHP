<?php

namespace BenMajor\GetAddressPHP\Entity;

use BenMajor\GetAddressPHP\Exception\AddressException;

class PrivateAddress extends Entity
{
	private $id;

	private $line1;
	private $line2;
	private $line3;
	private $line4;

	private $locality;
	private $townOrCity;
	private $county;
	private $postcode;

	public function __construct( string $postcode, $address = null )
	{
		$this->postcode = $postcode;

		if( !is_null($address) )
		{
			$this->id = $address->id;

			$this->line1 = (isset($address->line1)) ? $address->line1 : null;
			$this->line2 = (isset($address->line2)) ? $address->line2 : null;
			$this->line3 = (isset($address->line3)) ? $address->line3 : null;
			$this->line4 = (isset($address->line4)) ? $address->line4 : null;

			$this->locality = (isset($address->locality)) ? $address->locality : null;
			$this->townOrCity = (isset($address->townOrCity)) ? $address->townOrCity : null;
			$this->county = (isset($address->county)) ? $address->county : null;
		}
	}

	# Convert the Address to a request object:
	public function toRequestObject( $includeId = true )
	{
		$requestObject = [
			'line1' => $this->line1,
			'line2' => $this->line2,
			'line3' => $this->line3,
			'line4' => $this->line4,
			'locality' => $this->locality,
			'townOrCity' => $this->townOrCity,
			'county' => $this->county
		];

		if( $includeId )
		{
			$requestObject['id'] = $this->id;
		}

		return $requestObject;
	}
	
	public function __toString()
	{
		$address = array_filter([
			$this->line1,
			$this->line2,
			$this->line3,
			$this->line4,
			$this->townOrCity,
			$this->locality,
			$this->county,
			$this->postcode
		]);

		return implode($address, ', ');
	}
}