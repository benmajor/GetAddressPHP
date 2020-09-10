<?php

namespace BenMajor\GetAddressPHP\Entity;

class Permissions extends Entity
{
	protected $emailAddress;
	protected $expires;
	protected $permissions;

	public function __construct( $permissions = null )
	{
		$this->emailAddress = (is_object($permissions) && isset($permissions->email_address)) ? $permissions->email_address : null;
		$this->expires = (is_object($permissions) && isset($permissions->expires)) ? new \DateTime($permissions->expires) : null;

		if( is_object($permissions) && isset($permissions->permissions) )
		{
			$this->permissions = $permissions->permissions;
		}
		else
		{
			$perms = new \stdClass();
			$perms->view_invoices = false;
			$perms->unsubscribe = false;
			$perms->update_card_details = false;
		}
	}

	# Set the viewInvoices permission:
	public function setViewInvoices( bool $viewInvoices = true )
	{
		$this->permissions->view_invoices = $viewInvoices;

		# Return object to preserve method chaining:
		return $this;
	}

	# Set the unsubscribe permission:
	public function setUnsubscribe( bool $unsubscribe = true )
	{
		$this->permissions->unsubscribe = $unsubscribe;

		# Return object to preserve method chaining:
		return $this;
	}

	# Set the updateCardDetails permission:
	public function setUpdateCardDetails( bool $updateCardDetails = true )
	{
		$this->permissions->update_card_details = $updateCardDetails;

		# Return object to preserve method chaining:
		return $this;
	}

	# Set the expires param:
	public function setExpires( \DateTime $timestamp )
	{
		$this->expires = $timestamp->format('Y-m-d\TH:i:s.000\Z');
 
		# Return object to preserve method chaining:
		return $this;
	}

	# Get the viewInvoices permission:
	public function getViewInvoices()
	{
		return $this->permissions->view_invoices;
	}

	# Get the unsubscribe permission:
	public function getUnsubscribe()
	{
		return $this->permissions->unsubscribe;
	}

	# Get the updateCardDetails permission:
	public function getUpdateCardDetails()
	{
		return $this->permissions->update_card_details;
	}

	# Export the permissions to JSON (suitable for passing in body):
	public function toJSON()
	{
		return [
			'email_address' => $this->getEmailAddress(),
			'expires' => $this->getExpires(),
			'permissions' = [
				'view_invoices' => $this->getViewInvoices(),
				'unsubscribe' => $this->getUnsubscribe(),
				'update_card_details' => $this->getUpdateCardDetails()
			]
		];
	}

	public function __toString()
	{
		return json_encode($this->toJSON);
	}
}