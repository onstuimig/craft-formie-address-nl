<?php
namespace onstuimig\FormieAddressNL\gql\types;

use craft\gql\base\ObjectType;
use craft\gql\GqlEntityRegistry;

use GraphQL\Type\Definition\Type;

class AddressNLType extends ObjectType
{
    // Static Methods
    // =========================================================================

    public static function getName(): string
    {
        return 'AddressNLType';
    }

    public static function getType()
    {
        return GqlEntityRegistry::getEntity(self::getName()) ?: GqlEntityRegistry::createEntity(self::getName(), new self([
            'name' => self::getName(),
            'fields' => [
                'fullAddress' => [
                    'name' => 'fullAddress',
                    'type' => Type::string(),
                    'description' => 'The full address value.',
                    'resolve' => function($value) {
                        return (string)$value;
                    },
                ],
                'street' => [
                    'name' => 'street',
                    'type' => Type::string(),
                    'description' => 'The street value of the address.',
                ],
                'houseNumber' => [
                    'name' => 'houseNumber',
                    'type' => Type::string(),
                    'description' => 'The house number value of the address.',
                ],
                'houseNumberAddition' => [
                    'name' => 'houseNumberAddition',
                    'type' => Type::string(),
                    'description' => 'The house number addition value of the address.',
                ],
                'city' => [
                    'name' => 'city',
                    'type' => Type::string(),
                    'description' => 'The city value of the address.',
                ],
                'province' => [
                    'name' => 'province',
                    'type' => Type::string(),
                    'description' => 'The province value of the address.',
                ],
                'postalCode' => [
                    'name' => 'postalCode',
                    'type' => Type::string(),
                    'description' => 'The postal code value of the address.',
                ],
                'country' => [
                    'name' => 'country',
                    'type' => Type::string(),
                    'description' => 'The country value of the address.',
                ],
            ],
        ]));
    }
}
