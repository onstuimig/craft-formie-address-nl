<?php
namespace onstuimig\FormieAddressNL\gql\types\input;

use onstuimig\FormieAddressNL\fields\AddressNL as AddressField;
use onstuimig\FormieAddressNL\models\Address as AddressModel;

use craft\gql\GqlEntityRegistry;

use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\Type;

class AddressNLInputType extends InputObjectType
{
    // Static Methods
    // =========================================================================

    public static function getType(AddressField $context): mixed
    {
        /** @var AddressField $context */
        $typeName = $context->getForm()->handle . '_' . $context->handle . '_FormieAddressNLInput';

        if ($inputType = GqlEntityRegistry::getEntity($typeName)) {
            return $inputType;
        }

        $fields = [];

        foreach ($context->getFields() as $subField) {
            if ($subField->enabled) {
                $fields[$subField->handle] = [
                    'name' => $subField->handle,
                    'type' => $subField->required ? Type::nonNull(Type::string()) : Type::string(),
                ];
            }
        }

        return GqlEntityRegistry::createEntity($typeName, new InputObjectType([
            'name' => $typeName,
            'fields' => function() use ($fields) {
                return $fields;
            },
            'normalizeValue' => [self::class, 'normalizeValue'],
        ]));
    }

    public static function normalizeValue($value): mixed
    {
        if (!empty($value['name'])) {
            return $value['name'];
        }

        $addressModel = new AddressModel();
        $addressModel->street = $value['street'] ?? null;
        $addressModel->houseNumber = $value['houseNumber'] ?? null;
        $addressModel->houseNumberAddition = $value['houseNumberAddition'] ?? null;
        $addressModel->city = $value['city'] ?? null;
        $addressModel->province = $value['province'] ?? null;
        $addressModel->postalCode = $value['postalCode'] ?? null;
        $addressModel->country = $value['country'] ?? null;

        return $addressModel;
    }
}
