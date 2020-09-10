<?php

namespace BenMajor\GetAddressPHP\Entity;

use BenMajor\GetAddressPHP\Exception\EntityException;

class Entity
{
	# Magic getter:
	public function __call( $method, $args )
	{
		$start = substr($method, 0, 3);

		if( $start == 'get' || $start == 'set' )
		{
			$property = lcfirst(substr($method, 3));
			
			if( ! property_exists($this, $property) )
			{
				throw new EntityException(
					sprintf('Property %s does not exist in Entity.', $property)
				);
			}

			if( $start == 'set' )
			{
				$this->{$property} = $args[0];

				# Return object to preserve method-chaining:
				return $this;
			}

			return $this->{$property};
		}
	}
}