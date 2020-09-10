<?php

namespace BenMajor\GetAddressPHP\Entity;

class Invoice extends Entity
{
	protected $date;
	protected $number;
	protected $address;

	protected $gross;
	protected $tax;
	protected $net;

	protected $items = [ ];

	protected $pdfURL;

	function __construct( $invoice )
	{
		$this->date = new \DateTime($invoice->date);
		$this->number = $invoice->number;

		$this->address = new InvoiceAddress(
			$invoice->address_1,
			$invoice->address_2,
			$invoice->address_3,
			$invoice->address_4,
			$invoice->address_5,
			$invoice->address_6,
		);

		$this->gross = $invoice->total;
		$this->tax = $invoice->tax;
		$this->net = ($invoice->gross - $invoice->tax);

		$this->pdfURL = $invoice->pdf_url;

		foreach( $invoice->invoice_lines as $item )
		{
			$this->items[] = new InvoiceItem($item);
		}
	}

	# Get the formatted gross total:
	public function getFormattedGross( bool $includeCurrency = true )
	{
		return $this->formatPrice(
			$this->gross,
			$includeCurrency
		);
	}

	# Get the formatted tax amount:
	public function getFormattedTax( bool $includeCurrency = true )
	{
		return $this->formatPrice(
			$this->tax,
			$includeCurrency
		);
	}

	# Get the formatted net amount
	public function getFormattedNet( bool $includeCurrency = true )
	{
		return $this->formatPrice(
			$this->net,
			$includeCurrency
		);
	}

	# Format an number as a price:
	private function formatPrice( $price, $includeCurrency = true )
	{
		$formatted = number_format($price, 2, '.', ',');

		return ($includeCurrency) ? sprintf('£%', $formatted) : $formatted;
	}
}