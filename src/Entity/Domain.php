<?php

namespace BenMajor\GetAddressPHP\Entity;

use BenMajor\GetAddressPHP\Exception\AddressException;

class Domain extends Entity
{
	private $id;
	private $name;

	public function __construct( $domain )
	{
		$this->id = $domain->id;
		$this->name = $domin->name;
	}

	public function __toString()
	{
		return $this->name;
	}
}