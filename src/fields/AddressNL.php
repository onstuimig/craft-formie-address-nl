<?php
namespace onstuimig\FormieAddressNL\fields;


use onstuimig\FormieAddressNL\Plugin;
use onstuimig\FormieAddressNL\models\Address as AddressModel;
use onstuimig\FormieAddressNL\gql\types\AddressNLType;
use onstuimig\FormieAddressNL\gql\types\input\AddressNLInputType;

use verbb\formie\Formie;
use verbb\formie\base\Integration;
use verbb\formie\base\IntegrationInterface;
use verbb\formie\base\SubField;
use verbb\formie\helpers\SchemaHelper;
use verbb\formie\models\HtmlTag;
use verbb\formie\positions\AboveInput;
use verbb\formie\positions\Hidden as HiddenPosition;

use Craft;
use craft\base\ElementInterface;
use craft\base\PreviewableFieldInterface;
use craft\helpers\Json;

use Faker\Generator as FakerFactory;

use GraphQL\Type\Definition\Type;
use onstuimig\FormieAddressNL\interfaces\AddressNLProvider;
use yii\db\Schema;

class AddressNL extends SubField implements PreviewableFieldInterface 
{
	// Static Methods
	// =========================================================================

	public static function displayName(): string
	{
		$displayName = Plugin::getInstance()->getSettings()->fieldDisplayName ?? '';

		if (!$displayName) {
			return Craft::t('formie-address-nl', 'Address (NL)');
		}

		return Craft::t('formie', $displayName);
	}

	public static function getSvgIconPath(): string
	{
		return 'formie-address-nl/_field/icon.svg';
	}

	public static function dbType(): string
	{
		return Schema::TYPE_JSON;
	}

	public static function getFrontEndInputTemplatePath(): string
	{
		return 'formie-address-nl/_field/frontend';
	}

	public static function getEmailTemplatePath(): string
	{
		return 'formie-address-nl/_field/email';
	}

	// Public Methods
	// =========================================================================

	public function __construct(array $config = [])
	{
		unset(
			$config['autocompleteIntegration'],
			$config['autocompleteEnabled'],
			$config['autocompleteCollapsed'],
			$config['autocompleteLabel'],
			$config['autocompletePlaceholder'],
			$config['autocompleteDefaultValue'],
			$config['autocompletePrePopulate'],
			$config['autocompleteRequired'],
			$config['autocompleteErrorMessage'],
			$config['autocompleteCurrentLocation'],

			$config['streetEnabled'],
			$config['streetCollapsed'],
			$config['streetLabel'],
			$config['streetPlaceholder'],
			$config['streetDefaultValue'],
			$config['streetPrePopulate'],
			$config['streetRequired'],
			$config['streetErrorMessage'],
			$config['streetHidden'],

			$config['houseNumberEnabled'],
			$config['houseNumberCollapsed'],
			$config['houseNumberLabel'],
			$config['houseNumberPlaceholder'],
			$config['houseNumberDefaultValue'],
			$config['houseNumberPrePopulate'],
			$config['houseNumberRequired'],
			$config['houseNumberErrorMessage'],
			$config['houseNumberHidden'],

			$config['houseNumberAdditionEnabled'],
			$config['houseNumberAdditionCollapsed'],
			$config['houseNumberAdditionLabel'],
			$config['houseNumberAdditionPlaceholder'],
			$config['houseNumberAdditionDefaultValue'],
			$config['houseNumberAdditionPrePopulate'],
			$config['houseNumberAdditionRequired'],
			$config['houseNumberAdditionErrorMessage'],
			$config['houseNumberAdditionHidden'],

			$config['cityEnabled'],
			$config['cityCollapsed'],
			$config['cityLabel'],
			$config['cityPlaceholder'],
			$config['cityDefaultValue'],
			$config['cityPrePopulate'],
			$config['cityRequired'],
			$config['cityErrorMessage'],
			$config['cityHidden'],

			$config['provinceEnabled'],
			$config['provinceCollapsed'],
			$config['provinceLabel'],
			$config['provincePlaceholder'],
			$config['provinceDefaultValue'],
			$config['provincePrePopulate'],
			$config['provinceRequired'],
			$config['provinceErrorMessage'],
			$config['provinceHidden'],

			$config['postalCodeEnabled'],
			$config['postalCodeCollapsed'],
			$config['postalCodeLabel'],
			$config['postalCodePlaceholder'],
			$config['postalCodeDefaultValue'],
			$config['postalCodePrePopulate'],
			$config['postalCodeRequired'],
			$config['postalCodeErrorMessage'],
			$config['postalCodeHidden'],

			$config['countryEnabled'],
			$config['countryCollapsed'],
			$config['countryLabel'],
			$config['countryPlaceholder'],
			$config['countryDefaultValue'],
			$config['countryPrePopulate'],
			$config['countryRequired'],
			$config['countryErrorMessage'],
			$config['countryHidden'],
			$config['countryOptionLabel'],
			$config['countryOptionValue'],
		);

		$config['instructionsPosition'] = $config['instructionsPosition'] ?? AboveInput::class;

		return parent::__construct($config);
	}

	public function normalizeValue(mixed $value, ?ElementInterface $element): mixed
	{
		$value = parent::normalizeValue($value, $element);
		$value = Json::decodeIfJson($value);

		if ($value instanceof AddressModel) {
			return $value;
		}

		if (is_array($value)) {
			$address = new AddressModel($value);

			// Normalize country to null, due to it being a dropdown
			if ($address->country === '') {
				$address->country = null;
			}

			// Reset any disabled fields that might have content to null
			foreach ($this->getFields() as $field) {
				if ($field->getIsDisabled() && property_exists($address, $field->handle)) {
					$address->{$field->handle} = null;
				}
			}

			return $address;
		}

		return new AddressModel();
	}

	public function getPreviewInputHtml(): string
	{
		return Craft::$app->getView()->renderTemplate('formie-address-nl/_field/preview', [
			'field' => $this,
		]);
	}

	public function getAutocompleteHtml(array $renderOptions = []): string
	{
		$integration = $this->getAddressProviderIntegration();

		if (!$integration) {
			return '';
		}

		return $integration->getFrontEndHtml($this, $renderOptions);
	}

	public function getFrontEndJsModules(): ?array
	{
		$modules = parent::getFrontEndJsModules();

		$integration = $this->getAddressProviderIntegration();

		if (!$integration || !method_exists($integration, 'getFrontEndJsModules')) {
			return $modules;
		}

		$integrationModules = $integration->getFrontEndJsModules($this);

		if ($integrationModules) {
			if(!isset($integrationModules[0])) {
				$integrationModules = [$integrationModules];
			}

			$modules = array_merge(...$modules, ...$integrationModules);
		}

		return $modules;
	}

	public function getAddressProviderIntegration(): ?IntegrationInterface
	{
		$autocomplete = $this->getFieldByHandle('autocomplete');

		if (!$autocomplete || !$autocomplete->enabled || !$autocomplete->integrationHandle) {
			return null;
		}

		return Formie::$plugin->getIntegrations()->getIntegrationByHandle($autocomplete->integrationHandle);
	}

	public function supportsCurrentLocation(): bool
	{
		return false;
	}

	public function hasCurrentLocation(): bool
	{
		return false;
	}

	public function getContentGqlType(): Type|array
	{
		return AddressNLType::getType();
	}

	public function defineGeneralSchema(): array
	{
		return [
			SchemaHelper::labelField(),
			SchemaHelper::subFieldsConfigurationField([], [
				'type' => static::class,
			]),
		];
	}

	public function defineSettingsSchema(): array
	{
		return [
			SchemaHelper::includeInEmailField(),
		];
	}

	public function defineAppearanceSchema(): array
	{
		return [
			SchemaHelper::visibility(),
			SchemaHelper::labelPosition($this),
			SchemaHelper::subFieldLabelPosition(),
			SchemaHelper::instructions(),
			SchemaHelper::instructionsPosition($this),
		];
	}

	public function defineAdvancedSchema(): array
	{
		return [
			SchemaHelper::handleField(),
			SchemaHelper::cssClasses(),
			SchemaHelper::containerAttributesField(),
			SchemaHelper::enableContentEncryptionField(),
		];
	}

	public function defineConditionsSchema(): array
	{
		return [
			SchemaHelper::enableConditionsField(),
			SchemaHelper::conditionsField(),
		];
	}

	public function getContentGqlMutationArgumentType(): Type|array
	{
		return AddressNLInputType::getType($this);
	}

	public function defineHtmlTag(string $key, array $context = []): ?HtmlTag
	{
		$form = $context['form'] ?? null;

		$id = $this->getHtmlId($form);
		
		if ($key === 'fieldContainer') {
			return new HtmlTag('fieldset', [
				'class' => 'fui-fieldset',
				'aria-describedby' => $this->instructions ? "{$id}-instructions" : null,
			]);
		}

		if ($key === 'fieldLabel') {
			$labelPosition = $context['labelPosition'] ?? null;

			return new HtmlTag('legend', [
				'class' => [
					'fui-legend',
				],
				'data' => [
					'field-label' => true,
					'fui-sr-only' => $labelPosition instanceof HiddenPosition ? true : false,
				],
			]);
		}

		if ($key === 'locationLink') {
			return new HtmlTag('a', [
				'href' => 'javascript:;',
				'class' => 'fui-link fui-address-location-link',
				'text' => Craft::t('formie', 'Use my location'),
				'data-fui-address-location-btn' => true,
			]);
		}

		return parent::defineHtmlTag($key, $context);
	}


	// Protected Methods
	// =========================================================================

	protected function defineSubFields(): array
    {
		$addressProviderOptions = $this->_getAddressProviderOptions();

		$fields = [
			[
				'fields' => [
					[
						'type' => subfields\AddressNLPostalCode::class,
						'label' => 'Postcode',
						'handle' => 'postalCode',
						'enabled' => true,
						'required' => true,
						'labelPosition' => $this->subFieldLabelPosition,
						'inputAttributes' => [
							[
								'label' => 'autocomplete',
								'value' => 'postal-code',
							],
						],
					],
					[
						'type' => subfields\AddressNLHouseNumber::class,
						'label' => 'Huisnummer',
						'handle' => 'houseNumber',
						'enabled' => true,
						'required' => true,
						'labelPosition' => $this->subFieldLabelPosition,
						'inputAttributes' => [
							[
								'label' => 'autocomplete',
								'value' => 'address-line2',
							],
						],
					],
					[
						'type' => subfields\AddressNLHouseNumberAddition::class,
						'label' => 'Toevoeging',
						'handle' => 'houseNumberAddition',
						'enabled' => true,
						'labelPosition' => $this->subFieldLabelPosition,
						'inputAttributes' => [
							// [
							// 	'label' => 'autocomplete',
							// 	'value' => 'address-line3',
							// ],
						],
					],
				],
			],
			[
				'fields' => [
					[
						'type' => subfields\AddressNLStreet::class,
						'label' => 'Straatnaam',
						'handle' => 'street',
						'enabled' => true,
						'required' => true,
						'labelPosition' => $this->subFieldLabelPosition,
						'inputAttributes' => [
							[
								'label' => 'autocomplete',
								'value' => 'address-line1',
							],
						],
					],
					[
						'type' => subfields\AddressNLCity::class,
						'label' =>  'Plaats',
						'handle' => 'city',
						'enabled' => true,
						'required' => true,
						'labelPosition' => $this->subFieldLabelPosition,
						'inputAttributes' => [
							[
								'label' => 'autocomplete',
								'value' => 'address-level2',
							],
						],
					],
				],
			],
			[
				'fields' => [
					[
						'type' => subfields\AddressNLProvince::class,
						'label' => 'Provincie / Staat',
						'handle' => 'province',
						'enabled' => false,
						'labelPosition' => $this->subFieldLabelPosition,
						'inputAttributes' => [
							[
								'label' => 'autocomplete',
								'value' => 'address-level1',
							],
						],
					],
					[
						'type' => subfields\AddressNLCountry::class,
						'label' => 'Land',
						'handle' => 'country',
						'enabled' => false,
						'placeholder' => Craft::t('formie', 'Select an option'),
						'labelPosition' => $this->subFieldLabelPosition,
						'inputAttributes' => [
							[
								'label' => 'autocomplete',
								'value' => 'country',
							],
						],
					],
				],
			],
		];

		if ($addressProviderOptions) {
			$initialProvider = $addressProviderOptions[0]['value'] ?? null;
			array_push($fields, [
				'fields' => [
					[
						'type' => subfields\AddressNLAutocomplete::class,
						'label' => Craft::t('formie-address-nl', 'Autocomplete'),
						'handle' => 'autocomplete',
						'enabled' => $initialProvider ? true : false,
						'integrationHandle' => $initialProvider,
						// 'labelPosition' => $this->subFieldLabelPosition,
						// 'inputAttributes' => [
						// 	[
						// 		'label' => 'data-autocomplete',
						// 		'value' => true,
						// 	],
						// ],
					],
				],
			]);
		}

		return $fields;
	}

	protected function cpInputHtml(mixed $value, ?ElementInterface $element, bool $inline): string
	{
		return Craft::$app->getView()->renderTemplate('formie/_formfields/address/input', [
			'name' => $this->handle,
			'value' => $value,
			'field' => $this,
			'element' => $element,
		]);
	}

	protected function defineValueForEmailPreview(FakerFactory $faker): mixed
	{
		return new AddressModel([
			'street' => $faker->streetName,
			'houseNumber' => $faker->buildingNumber,
			'houseNumberAddition' => $faker->randomLetter,
			'city' => $faker->city,
			'postalCode' => $faker->postcode,
			'province' => $faker->state,
			'country' => AddressModel::nameToCode($faker->country),
		]);
	}


	// Private Methods
	// =========================================================================

	private function _getAddressProviderOptions(): array
    {
        $addressProviderOptions = [];

		/** @var Integration[] $addressProviders */
        $addressProviders = Formie::$plugin->getIntegrations()->getAllIntegrationsForType(Integration::TYPE_ADDRESS_PROVIDER);

        foreach ($addressProviders as $addressProvider) {
			if(!($addressProvider instanceof AddressNLProvider)) {
				continue;
			}

            if ($addressProvider->getEnabled()) {
                $addressProviderOptions[] = ['label' => $addressProvider->getName(), 'value' => $addressProvider->getHandle()];
            }
        }

        return $addressProviderOptions;
    }
}
