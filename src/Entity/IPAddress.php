<?php

namespace BenMajor\GetAddressPHP\Entity;

use BenMajor\GetAddressPHP\Exception\IPAddressException;

class IPAddress extends Entity
{
	protected $id;
	protected $value;

	public function __construct( string $value, $id = null )
	{
		if( ! filter_var($id, FILTER_VALIDATE_IP) )
		{
			throw new IPAddressException('Specified IP address value is invalid.');
		}

		$this->value = $value;
		$this->id = $id;
	}

	public function __toString()
	{
		return $this->value;
	}
}