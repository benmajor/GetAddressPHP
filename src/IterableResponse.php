<?php

namespace BenMajor\GetAddressPHP;

class IterableResponse
{
	private $items = 0;
	private $pointer = 0;
	private $raw;
	private $length = 0;

	function __construct( array $response = [ ] )
	{
		$this->items = [ ];

		foreach( $response as $item )
		{
			$this->items[] = new Response($item);
		}

		$this->length = count($this->items);
	}

	# Reset the current pointer (and return the first item):
	public function reset()
	{
		$this->pointer = 0;

		return $this->current();
	}

	# Return the last element:
	public function last()
	{
		$this->pointer = ($this->length - 1);

		return $this->current();
	}

	# Get the current element and do not modify the cursor:
	public function current()
	{
		return $this->items[ $this->pointer ];
	}

	# Get the next item in the response and update the cursor:
	public function next()
	{
		if( $this->pointer == $this->length - 1 )
		{
			return null;
		}

		$this->pointer++;

		return $this->current();
	}

	# Get the previous item in the response and update the cursor:
	public function prev()
	{
		if( $this->pointer == 0 )
		{
			return null;
		}

		$this->pointer--;

		return $this->current();
	}

	# Return the length of results:
	public function count()
	{
		return $this->length;
	}

	# Add a new item to the iterable response:
	public function addItem( $item )
	{
		$this->items[] = $item;
		$this->length++;

		# Return object to preserve method chaining:
		return $this;
	}

	# Iterate over the result:
	public function each( callable $callback )
	{
		foreach( $this->items as $item )
		{
			call_user_func($callback, $item, $this->pointer);

			$this->pointer++;
		}
	}
}