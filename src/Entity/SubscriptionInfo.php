<?php

namespace BenMajor\GetAddressPHP\Entity;

class SubscriptionInfo extends Entity
{
	protected $expiryDate;
	protected $firstDailyLimit;
	protected $secondDailyLimit;
	protected $amount;
	protected $term;

	function __construct( $info )
	{
		$this->expiryDate = new \DateTime($info->expiry_date);
		$this->firstDailyLimit = $info->first_daily_limit;
		$this->secondDailyLimit = $info->second_daily_limit;
		$this->amount = $info->amount;
		$this->term = $info->term;
	}
}