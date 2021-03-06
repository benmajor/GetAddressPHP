<?php

namespace BenMajor\GetAddressPHP\Entity;

use BenMajor\GetAddressPHP\Exception\AddressException;

class Address extends Entity
{
	protected $buildingName;
	protected $buildingNumber;
	protected $subBuildingName;
	protected $subBuildingNumber;

	protected $thoroughfare;
	protected $line1;
	protected $line2;
	protected $line3;
	protected $line4;

	protected $locality;
	protected $townOrCity;
	protected $county;
	protected $district;
	protected $country;

	protected $postcode;

	protected $formatted;

	public function __construct( $address, string $postcode = null )
	{
		$this->formatted = $address->formatted_address;
		$this->formatted[] = $postcode;

		# Now bind everything:
		$this->buildingName = $address->building_name;
		$this->buildingNumber = $address->building_number;
		$this->subBuildingNumber = $address->sub_building_number;
		$this->subBuildingName = $address->sub_building_name;
		
		$this->thoroughfare = $address->thoroughfare;
		$this->line1 = $address->line_1;
		$this->line2 = $address->line_2;
		$this->line3 = $address->line_3;
		$this->line4 = $address->line_4;

		$this->locality = $address->locality;
		$this->townOrCity = $address->town_or_city;
		$this->county = $address->county;
		$this->district = $address->district;
		$this->country = $address->country;

		$this->postcode = $postcode;		
	}

	public function __toString()
	{
		return implode(array_filter($this->formatted), ', ');
	}
}