<?php

namespace BenMajor\GetAddressPHP\Entity;

class InvoiceAddress extends Entity
{
	protected $line1;
	protected $line2;
	protected $line3;
	protected $line4;
	protected $line5;
	protected $line6;

	private $array = [ ];

	function __construct( $line1, $line2, $line3, $line4, $line5, $line6 )
	{
		$this->line1 = $line1;
		$this->line2 = $line2;
		$this->line3 = $line3;
		$this->line4 = $line4;
		$this->line5 = $line5;
		$this->line6 = $line6;

		$this->array = array_filter([
			$this->line1,
			$this->line2,
			$this->line3,
			$this->line4,
			$this->line5,
			$this->line6
		]);
	}

	public function output( $sep = '<br />', $addressTag = true )
	{
		if( $addressTag )
		{
			return sprintf('<address>%s</address>', implode($sep, $this->array));
		}

		return implode($sep, $this->array);
	}

	public function __toString()
	{
		return $this->output();
	}
}