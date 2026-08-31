<?php
namespace onstuimig\FormieAddressNL\models;

use Craft;
use craft\base\Model;

class AddressNLProviderResult extends Model
{

    public string $street;
    public ?string $houseNumber = null;
    public ?string $houseNumberAddition = null;
	public string $city;
	public ?string $province = null;
	public ?string $postalCode = null;
	public ?string $country = null;

}
