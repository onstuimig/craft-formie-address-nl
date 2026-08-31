<?php
namespace onstuimig\FormieAddressNL\fields\subfields;

use verbb\formie\base\SubFieldInnerFieldInterface;
use verbb\formie\fields\SingleLineText;

use Craft;
use craft\base\ElementInterface;

class AddressNLPostalCode extends SingleLineText implements SubFieldInnerFieldInterface
{
    // Static Methods
    // =========================================================================

    public static function displayName(): string
    {
        return Craft::t('formie-address-nl', 'Address (NL) - Postal Code');
    }

    public static function getFrontEndInputTemplatePath(): string
    {
        return 'fields/single-line-text';
    }

    public static function getEmailTemplatePath(): string
    {
        return 'fields/single-line-text';
    }

    
    // Public Methods
    // =========================================================================

    public function getElementValidationRules(): array
    {
        $rules = parent::getElementValidationRules();
        $rules[] = [$this->handle, 'validatePostalCode'];

        return $rules;
    }

    public function validatePostalCode(ElementInterface $element): void
    {
        $value = $element->getFieldValue($this->fieldKey);

        if (strlen($value) > 10) {
            $element->addError($this->fieldKey, Craft::t('formie', '"{label}" should contain at most {max, number} {max, plural, one{character} other{characters}}.', [
                'label' => $this->label,
                'max' => 10,
            ]));
        }
    }
}
