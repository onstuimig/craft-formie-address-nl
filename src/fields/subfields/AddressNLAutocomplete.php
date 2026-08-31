<?php
namespace onstuimig\FormieAddressNL\fields\subfields;

use verbb\formie\Formie;
use verbb\formie\base\Integration;
use verbb\formie\base\SubFieldInnerFieldInterface;
use verbb\formie\fields\SingleLineText;
use verbb\formie\fields\Html;
use verbb\formie\helpers\SchemaHelper;

use Craft;
use craft\base\ElementInterface;
use craft\helpers\UrlHelper;
use onstuimig\FormieAddressNL\fields\AddressNL;
use verbb\formie\base\CosmeticField;
use verbb\formie\models\HtmlTag;

class AddressNLAutocomplete extends CosmeticField implements SubFieldInnerFieldInterface
{
	// Static Methods
	// =========================================================================

	public static function displayName(): string
	{
		return Craft::t('formie-address-nl', 'Address (NL) - Autocomplete');
	}

	public static function getFrontEndInputTemplatePath(): string
	{
		return 'formie-address-nl/_field/autocomplete';
	}

	public function getFrontEndJsModules(): ?array
	{
		if(Craft::$app->getRequest()->getIsWebRequest() && Craft::$app->getRequest()->getIsSiteRequest()) {
			Craft::$app->getView()->registerCss('[data-field-type="address-nl"] [data-autocomplete-hidden] { display: none; }');
		}
		
		/** @var AddressNL|null $addressField */
		$addressField = $this->getParentField();

		/* $form = $this->getForm() ?? $addressField->getForm();

		if (!$form || !$addressField) {
			return null;
		} */

		$actionUrl = UrlHelper::actionUrl('formie-address-nl/autocomplete', [
			// 'formUid' => $form->getCanonicalUid(),
			'fieldUid' => $addressField->uid,
		], null, false);
		
		return [
			'src' => Craft::$app->getAssetManager()->getPublishedUrl('@onstuimig/FormieAddressNL/web/assets/frontend/dist/', true, 'autocomplete.js'),
			'module' => 'FormieAddressNLAutocomplete',
			'settings' => [
				'actionUrl' => $actionUrl,
			],
		];
	}

	public function getPreviewInputHtml(): string
	{
		return '';
	}

	protected function cpInputHtml(mixed $value, ?ElementInterface $element, bool $inline): string
	{
		return '';
	}


	// Properties
	// =========================================================================

	public ?string $integrationHandle = null;
	public bool $currentLocation = false;


	// Public Methods
	// =========================================================================

	public function attributeLabels(): array
	{
		$labels = parent::attributeLabels();
		$labels['integrationHandle'] = Craft::t('formie-address-nl', 'Autocomplete Integration');

		return $labels;
	}

	public function defineGeneralSchema(): array
	{
		$fields = parent::defineGeneralSchema();

		$addressProviderOptions = $this->_getAddressProviderOptions();

		array_unshift($fields, SchemaHelper::selectField([
			'label' => Craft::t('formie-address-nl', 'Autocomplete Integration'),
			'help' => Craft::t('formie', 'Select which address provider this field should use.'),
			'name' => 'integrationHandle',
			'validation' => 'required',
			'required' => true,
			'options' => array_merge(
				[['label' => Craft::t('formie', 'Select an option'), 'value' => '']],
				$addressProviderOptions
			),
		]));

		$fields[] = SchemaHelper::lightswitchField([
			'label' => Craft::t('formie', 'Show Current Location Button'),
			'help' => Craft::t('formie', 'Whether this field should show a "Use my location" button.'),
			'name' => 'currentLocation',
			'if' => '$get(integrationHandle).value == googlePlaces',
		]);

		return $fields;
	}

	public function defineHtmlTag(string $key, array $context = []): ?HtmlTag
	{
		// $form = $context['form'] ?? null;
		// $errors = $context['errors'] ?? null;

		// $id = $this->getHtmlId($form);
		// $dataId = $this->getHtmlDataId($form);

		if ($key === 'fieldAutocompleteResult') {
			return new HtmlTag('div', array_merge([
				'class' => [
					'fui-addressnl-autocomplete',
					// $errors ? 'fui-error' : false,
				],
				'data' => [
					'address-nl-autocomplete-result' => true,
					// 'fui-message' => Craft::t('formie', $this->errorMessage) ?: null,
				],
			], $this->getInputAttributes()));
		}

		return parent::defineHtmlTag($key, $context);
	}


	// Protected Methods
	// =========================================================================

	protected function defineRules(): array
	{
		$rules = parent::defineRules();

		// Add back when we can figure out how to enforce it with enabled state better
		// $rules[] = [['integrationHandle'], 'required'];

		return $rules;
	}


	// Private Methods
	// =========================================================================

	private function _getAddressProviderOptions(): array
	{
		$addressProviderOptions = [];
		$addressProviders = Formie::$plugin->getIntegrations()->getAllIntegrationsForType(Integration::TYPE_ADDRESS_PROVIDER);

		foreach ($addressProviders as $addressProvider) {
			if ($addressProvider->getEnabled()) {
				$addressProviderOptions[] = [
					'label' => $addressProvider->getName(),
					'value' => $addressProvider->getHandle(),
					'data-type' => get_class($addressProvider),
				];
			}
		}

		return $addressProviderOptions;
	}
}
