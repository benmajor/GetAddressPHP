<?php

namespace BenMajor\GetAddressPHP\Entity;

use BenMajor\GetAddressPHP\Exception\UnitException;

class DistanceResult extends Entity
{
	private $raw;

	private $from;
	private $to;
	private $distance;

	protected $allowedMeasurements = [
		'm', 'metres', 'meters',
		'km', 'kilometres', 'kilometers',
		'miles', 'mile', 
		'yds', 'yards',
		'ft', 'feet'
	];

	public function __construct( $result )
	{
		$this->raw = $result;

		$this->from = $result->from;
		$this->to = $result->to;
		$this->distance = $result->metres;
	}

	# Get the body of the result:
	public function getBody()
	{
		return json_encode($this->raw);
	}

	# Get the parsed body of the result:
	public function getParsedBody()
	{
		return $this->raw;
	}

	# Get the from latitude:
	public function getFromLatitude()
	{
		return $this->from->latitude;
	}

	# Get the from longitude:
	public function getFromLongitude()
	{
		return $this->from->longitude;
	}

	# Get the from postcode:
	public function getFromPostcode()
	{
		return $this->from->postcode;
	}

	# Get the to latitude:
	public function getToLatitude()
	{
		return $this->to->latitude;
	}

	# Get the to longitude:
	public function getToLongitude()
	{
		return $this->to->longitude;
	}

	# Get the to postcode:
	public function getToPostcode()
	{
		return $this->to->postcode;
	}

	# Get the distance (optionally converted to another format)
	public function getDistance( string $measurement = 'm' )
	{
		if( ! in_array(strtolower($measurement), $this->allowedMeasurements) )
		{
			throw new UnitException('Specified measurement is invalid. Must be one of: '.implode($this->allowedMeasurements, ', '));
		}

		switch( strtolower($measurement) )
		{
			case 'm':
			case 'metres':
			default:
				return $this->distance;
				break;

			case 'km':
			case 'kilometers':
			case 'kilometres':
				return $this->distance / 1000;
				break;

			case 'miles':
			case 'mile':
				return ($this->distance * 0.000621371);
				break;

			case 'yds':
			case 'yards':
				return ($this->distance * 1.09361);
				break;

			case 'ft':
			case 'feet':
				return ($this->distance * 3.28084);
				break;
		}
	}

	public function __toString()
	{
		return implode(array_filter($this->formatted), ', ');
	}
}