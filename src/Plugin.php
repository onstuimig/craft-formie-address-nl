<?php

namespace onstuimig\FormieAddressNL;

use Craft;
use craft\base\Model;
use craft\base\Plugin as BasePlugin;
use onstuimig\FormieAddressNL\fields\AddressNL;
use onstuimig\FormieAddressNL\fields\subfields\AddressNLAutocomplete;
use onstuimig\FormieAddressNL\fields\subfields\AddressNLCity;
use onstuimig\FormieAddressNL\fields\subfields\AddressNLCountry;
use onstuimig\FormieAddressNL\fields\subfields\AddressNLHouseNumber;
use onstuimig\FormieAddressNL\fields\subfields\AddressNLHouseNumberAddition;
use onstuimig\FormieAddressNL\fields\subfields\AddressNLPostalCode;
use onstuimig\FormieAddressNL\fields\subfields\AddressNLProvince;
use onstuimig\FormieAddressNL\fields\subfields\AddressNLStreet;
use onstuimig\FormieAddressNL\models\Settings;
use verbb\formie\events\RegisterFieldsEvent;
use verbb\formie\services\Fields;
use yii\base\Event;

/**
 * Formie Address NL plugin
 *
 * @method static Plugin getInstance()
 * @method Settings getSettings()
 * @author Onstuimig
 * @copyright Onstuimig
 * @license https://craftcms.github.io/license/ Craft License
 */
class Plugin extends BasePlugin
{
	public string $schemaVersion = '1.0.0';
	public bool $hasCpSettings = true;

	public static function config(): array
	{
		return [
			'components' => [
				// Define component configs here...
			],
		];
	}

	public function init(): void
	{
		parent::init();

		$this->attachEventHandlers();

		// Any code that creates an element query or loads Twig should be deferred until
		// after Craft is fully initialized, to avoid conflicts with other plugins/modules
		Craft::$app->onInit(function() {
			// ...
		});
	}

	protected function createSettingsModel(): ?Model
	{
		return Craft::createObject(Settings::class);
	}

	protected function settingsHtml(): ?string
	{
		 // Get the settings that are being defined by the config file
		$overrides = Craft::$app->getConfig()->getConfigFromFile(strtolower($this->handle));

		return Craft::$app->view->renderTemplate('formie-address-nl/_settings.twig', [
			'plugin' => $this,
			'settings' => $this->getSettings(),
			'overrides' => array_keys($overrides),
		]);
	}

	private function attachEventHandlers(): void
	{
		Event::on(
			Fields::class,
			Fields::EVENT_REGISTER_FIELDS,
			function(RegisterFieldsEvent $event) {
				$event->fields[] = AddressNL::class;
				$event->fields[] = AddressNLStreet::class;
				$event->fields[] = AddressNLHouseNumber::class;
				$event->fields[] = AddressNLHouseNumberAddition::class;
				$event->fields[] = AddressNLPostalCode::class;
				$event->fields[] = AddressNLCity::class;
				$event->fields[] = AddressNLProvince::class;
				$event->fields[] = AddressNLCountry::class;
				$event->fields[] = AddressNLAutocomplete::class;
			}
		);
	}
}
