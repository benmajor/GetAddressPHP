<?php

namespace BenMajor\GetAddressPHP\Entity;

class Suggestion extends Entity
{
	protected $address;
	protected $id;
	protected $url;

	public function __construct( $suggestion, string $baseURL )
	{
		$this->address = $suggestion->address;
		$this->id = $suggestion->id;
		$this->url = $baseURL.$suggestion->url;
	}

	public function __toString()
	{
		return $this->address;
	}
}