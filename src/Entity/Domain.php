<?php

namespace BenMajor\GetAddressPHP\Entity;

use BenMajor\GetAddressPHP\Exception\DomainException;

class Domain extends Entity
{
	protected $id;
	protected $name;

	public function __construct( string $domain, $id = null )
	{
		if( ! filter_var($domain, FILTER_VALIDATE_URL) )
		{
			throw new DomainException('Specified domain name is invalid.');
		}

		$this->id = $id;
		$this->name = $domain;
	}

	public function __toString()
	{
		return $this->name;
	}
}