<?php

namespace BenMajor\GetAddressPHP\Entity;

use BenMajor\GetAddressPHP\Exception\EmailException;

class EmailAddress extends Entity
{
	protected $id;
	protected $address;

	public function __construct( string $address, $id = null )
	{
		if( ! filter_var($email, FILTER_VALIDATE_EMAIL) )
		{
			throw new EmailException('The specified email address is invalid.');
		}

		$this->address = $address;
		$this->id = $id;
	}

	public function __toString()
	{
		return $this->address;
	}
}