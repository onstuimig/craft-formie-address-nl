<?php
namespace onstuimig\FormieAddressNL\interfaces;

use onstuimig\FormieAddressNL\models\AddressNLProviderResult;

interface AddressNLProvider
{
	public function completeAddress(string $postalCode, string $houseNumber, ?string $houseNumberAddition = null): ?AddressNLProviderResult;
}
