<?php
namespace onstuimig\FormieAddressNL\fields\subfields;

use Craft;
use verbb\formie\fields\subfields\AddressCountry;

class AddressNLCountry extends AddressCountry
{
    // Static Methods
    // =========================================================================

    public static function displayName(): string
    {
        return Craft::t('formie-address-nl', 'Address (NL) - Country');
    }

}
