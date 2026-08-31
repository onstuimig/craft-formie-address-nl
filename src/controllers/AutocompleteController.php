<?php
namespace onstuimig\FormieAddressNL\controllers;

use Craft;
use craft\web\Controller;
use onstuimig\FormieAddressNL\fields\AddressNL;
use onstuimig\FormieAddressNL\fields\subfields\AddressNLAutocomplete;
use onstuimig\FormieAddressNL\interfaces\AddressNLProvider;
use verbb\formie\base\FieldInterface;
use verbb\formie\base\Integration;
use verbb\formie\elements\Form;
use verbb\formie\Formie;
use verbb\formie\records\Field as FieldRecord;

class AutocompleteController extends Controller
{
	private $debug = false;

	protected bool|array|int $allowAnonymous = ['index'];

	public function actionIndex(
		string $fieldUid, 
		string $postalCode, 
		string $houseNumber, 
		?string $houseNumberAddition = null
	) {
		$postalCode = trim(str_replace(' ', '', $postalCode));
		$houseNumber = trim($houseNumber);
		$houseNumberAddition = $houseNumberAddition ? trim($houseNumberAddition) : null;

		/** @var AddressNL|null $addressField */
		$addressField = $this->getFieldByUid($fieldUid);

		if (!$addressField) {
			return $this->asJson([]);
		}

		/** @var Integration|null $integration */
		$integration = $addressField->getAddressProviderIntegration();

		if (!$integration || !($integration instanceof AddressNLProvider)) {
			return $this->asJson([]);
		}

		/** @var AddressNLAutocomplete|null */
		$autocompleteField = $addressField->getFieldByHandle('autocomplete');

		if (!$autocompleteField || !$autocompleteField->enabled) {
			return $this->asJson([]);
		}

		$cache = Craft::$app->getCache();
		$cacheTtl = 7200;
		$cacheKey = md5(__CLASS__ . __FUNCTION__ . json_encode([
			'integrationHandle' => $integration->getHandle(),
			'postalCode' => $postalCode,
			'houseNumber' => $houseNumber,
			'houseNumberAddition' => $houseNumberAddition,
		]));
		$cachedValue = $cache->get($cacheKey);
		if ($cachedValue && !$this->debug) {
			return $this->asJson($cachedValue);
		}

		$result = $integration->completeAddress($postalCode, $houseNumber, $houseNumberAddition);

		if(!$result) {
			$result = [];
		}

		$cache->set($cacheKey, $result, $cacheTtl);
		return $this->asJson($result);
	}

	private function getFieldByUid(string $uid): ?FieldInterface
	{
		$fieldRecord = FieldRecord::findOne(['uid' => $uid])?->attributes ?? [];

		return $fieldRecord ? Formie::getInstance()->getFields()->createField($fieldRecord) : null;
	}

}