<?php

namespace onstuimig\FormieAddressNL\models;

use verbb\formie\base\FieldValueInterface;
use verbb\formie\fields\data\OptionData;
use verbb\formie\helpers\ArrayHelper;
use verbb\formie\helpers\StringHelper;

use Craft;
use craft\base\Model;

use CommerceGuys\Addressing\Country\CountryRepository;

class Address extends Model implements FieldValueInterface
{
	// Static Methods
	// =========================================================================

	public static function getCountries(string $indexBy = 'code'): array
	{
		$locale = Craft::$app->getLocale()->getLanguageID();
		$repo = new CountryRepository($locale);

		return $indexBy === 'name' ? array_flip($repo->getList()) : $repo->getList();
	}

	public static function codeToName(string $code): ?string
	{
		return self::getCountries('code')[$code] ?? null;
	}

	public static function nameToCode(string $name): ?string
	{
		return self::getCountries('name')[$name] ?? null;
	}

	// Properties
	// =========================================================================

	public null $autocomplete = null;
    public ?string $street = null;
    public ?string $houseNumber = null;
    public ?string $houseNumberAddition = null;
	public ?string $city = null;
	public ?string $province = null;
	public ?string $postalCode = null;
	public ?string $country = null;
	public ?string $countryOption = null;

	// Public Methods
	// =========================================================================
	
	public function __construct(array $config = [])
	{
		// Country should use the label, not value given it's a dropdown
		if (isset($config['country']) && $config['country'] instanceof OptionData) {
			$countryValue = $config['country']->value ?? '';

			if ($countryValue) {
				/** @disregard P1013 - 'getOptions' is always defined for OptionData */
				$countryOptions = $config['country']->getOptions();

				if ($countryOption = ArrayHelper::firstWhere($countryOptions, 'value', $countryValue)) {
					$config['countryOption'] = $countryOption->label ?? '';
				}
			}
		}

		parent::__construct($config);
	}
	
	public function __toString()
	{
		$address = ArrayHelper::filterEmptyStringsFromArray([
			StringHelper::trim($this->street ?? ''),
			StringHelper::trim($this->houseNumber ?? ''),
			StringHelper::trim($this->houseNumberAddition ?? ''),
			StringHelper::trim($this->postalCode ?? ''),
			StringHelper::trim($this->city ?? ''),
			StringHelper::trim($this->province ?? ''),
			StringHelper::trim($this->countryOption ?? ''),
		]);

		return implode(', ', $address);
	}

	public function isEmpty(): bool
	{
		return $this->__toString() === '';
	}

}
