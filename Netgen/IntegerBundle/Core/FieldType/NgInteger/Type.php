<?php
/**
 * File containing the NgInteger class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace Netgen\IntegerBundle\Core\FieldType\NgInteger;
use eZ\Publish\Core\FieldType\FieldType,
    eZ\Publish\Core\FieldType\ValidationError,
    eZ\Publish\SPI\Persistence\Content\FieldValue,
    eZ\Publish\API\Repository\Values\ContentType\FieldDefinition,
    eZ\Publish\Core\Base\Exceptions\InvalidArgumentType;

/**
 * The NgInteger field type.
 *
 * This field type represents a combination of two integers.
 */
class Type extends FieldType
{
    /**
     * Namespace for custom NgInteger validators
     */
    const VALIDATOR_NAMESPACE = "Netgen\\IntegerBundle\\Core\\FieldType\\Validator";

    /**
     * Validator object cache array
     *
     * @var array
     */
    protected $validators = array();

    /**
     * Validators supported by this field type
     *
     * @var string[]
     */
    protected $allowedValidators = array(
        "NgIntegerValueValidator"
    );

    /**
     * Return the field type identifier for this field type
     *
     * @return string
     */
    public function getFieldTypeIdentifier()
    {
        return "nginteger";
    }

    /**
     * Returns the name of the given field value.
     *
     * It will be used to generate content name and url alias if current field is designated
     * to be used in the content name/urlAlias pattern.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    public function getName( $value )
    {
        $value = $this->acceptValue( $value );
        return (string) $value;
    }

    /**
     * Returns the empty value for this field type.
     *
     * This value will be used, if no value was provided for a field of this
     * type and no default value was specified in the field definition.
     *
     * @return mixed
     */
    public function getEmptyValue()
    {
        return new Value();
    }

    /**
     * Potentially builds and checks the type and structure of the $inputValue.
     *
     * This method first inspects $inputValue, if it needs to convert it, e.g.
     * into a dedicated value object. An example would be, that the field type
     * uses values of MyCustomFieldTypeValue, but can also accept strings as
     * the input. In that case, $inputValue first needs to be converted into a
     * MyCustomFieldTypeClass instance.
     *
     * After that, the (possibly converted) value is checked for structural
     * validity. Note that this does not include validation after the rules
     * from validators, but only plausibility checks for the general data
     * format.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if the parameter is not of the supported value sub type
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException if the value does not match the expected structure
     *
     * @param mixed $inputValue
     *
     * @return mixed The potentially converted and structurally plausible value.
     */
    public function acceptValue( $inputValue )
    {
        if ( is_array( $inputValue ) || count( $inputValue ) == 2 )
        {
            $inputValue = new Value( $inputValue[0], $inputValue[1] );
        }

        if (! $inputValue instanceof Value )
        {
            throw new InvalidArgumentType(
                '$inputValue',
                'Netgen\\IntegerBundle\\Core\\FieldType\\NgInteger\\Value',
                $inputValue
            );
        }

        if ( !is_int( $inputValue->firstNumber ) )
        {
            throw new InvalidArgumentType(
                '$inputValue->firstNumber',
                'int',
                $inputValue->firstNumber
           );
        }

        if ( !is_int( $inputValue->secondNumber ) )
        {
            throw new InvalidArgumentType(
                '$inputValue->secondNumber',
                'int',
                $inputValue->secondNumber
           );
        }

        return $inputValue;
    }

    /**
     * Returns information for FieldValue->$sortKey relevant to the field type.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    protected function getSortInfo( $value )
    {
        return $value->firstNumber;
    }

    /**
     * Converts an $hash to the Value defined by the field type
     *
     * @param mixed $hash
     *
     * @return mixed
     */
    public function fromHash( $hash )
    {
        $firstNumber = isset( $hash["firstNumber"] ) ? $hash["firstNumber"] : null;
        $secondNumber = isset( $hash["secondNumber"] ) ? $hash["secondNumber"] : null;

        return new Value( $firstNumber, $secondNumber );
    }

    /**
     * Converts a Value to a hash
     *
     * @param mixed $value
     *
     * @return mixed
     */
    public function toHash( $value )
    {
        return array(
            "firstNumber" => $value->firstNumber,
            "secondNumber" => $value->secondNumber
        );
    }

    /**
     * Converts a $value to a persistence value.
     *
     * In this method the field type puts the data which is stored in the field of content in the repository
     * into the property FieldValue::data. The format of $data is a primitive, an array (map) or an object, which
     * is then canonically converted to e.g. json/xml structures by future storage engines without
     * further conversions. For mapping the $data to the legacy database an appropriate Converter
     * (implementing eZ\Publish\Core\Persistence\Legacy\FieldValue\Converter) has implemented for the field
     * type. Note: $data should only hold data which is actually stored in the field. It must not
     * hold data which is stored externally.
     *
     * The $externalData property in the FieldValue is used for storing data externally by the
     * FieldStorage interface method storeFieldData.
     *
     * The FieldValuer::sortKey is build by the field type for using by sort operations.
     *
     * @see \eZ\Publish\SPI\Persistence\Content\FieldValue
     *
     * @param mixed $value The value of the field type
     *
     * @return \eZ\Publish\SPI\Persistence\Content\FieldValue the value processed by the storage engine
     */
    public function toPersistenceValue( $value )
    {
        if ( $value === null )
        {
            return new FieldValue(
                array(
                    "data" => null,
                    "externalData" => null,
                    "sortKey" => null,
                )
            );
        }

        return new FieldValue(
            array(
                "data" => array(
                    "firstNumber" => $value->firstNumber,
                    "secondNumber" => $value->secondNumber
                ),
                "externalData" => array(
                    "firstNumber" => $value->firstNumber,
                    "secondNumber" => $value->secondNumber
                ),
                "sortKey" => $this->getSortInfo( $value ),
            )
        );
    }

    /**
     * Converts a persistence $fieldValue to a Value
     *
     * This method builds a field type value from the $data and $externalData properties.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\FieldValue $fieldValue
     *
     * @return mixed
     */
    public function fromPersistenceValue( FieldValue $fieldValue )
    {
        return new Value(
            $fieldValue->externalData["firstNumber"],
            $fieldValue->externalData["secondNumber"]
        );
    }

    /**
     * Returns a schema for supported validator configurations.
     *
     * This implementation returns a three dimensional map containing for each validator configuration
     * referenced by identifier a map of supported parameters which are defined by a type and a default value
     * (see example).
     * Example:
     * <code>
     *  array(
     *      'stringLength' => array(
     *          'minStringLength' => array(
     *              'type'    => 'int',
     *              'default' => 0,
     *          ),
     *          'maxStringLength' => array(
     *              'type'    => 'int'
     *              'default' => null,
     *          )
     *      ),
     *  );
     * </code>
     */
    public function getValidatorConfigurationSchema()
    {
        $validatorConfigurationSchema = array();
        foreach ( $this->allowedValidators as $validatorIdentifier )
        {
            $validator = $this->getValidator( $validatorIdentifier );
            $validatorConfigurationSchema[$validatorIdentifier] = $validator->getConstraintsSchema();
        }

        return $validatorConfigurationSchema;
    }

    /**
     * Validates a field based on the validators in the field definition
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     *
     * @param \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition $fieldDefinition The field definition of the field
     * @param \eZ\Publish\Core\FieldType\Value $fieldValue The field for which an action is performed
     *
     * @return \eZ\Publish\SPI\FieldType\ValidationError[]
     */
    public function validate( FieldDefinition $fieldDefinition, $fieldValue )
    {
        $validatorConfiguration = $fieldDefinition->getValidatorConfiguration();
        if ( !is_array( $validatorConfiguration ) )
        {
            return array();
        }

        $validationErrors = array();

        foreach ( $validatorConfiguration as $validatorIdentifier => $parameters )
        {
            $validator = $this->getValidator( $validatorIdentifier );
            $validator->initializeWithConstraints( $parameters );

            if ( !$validator->validate( $fieldValue ) )
            {
                $validationErrors += $validator->getMessage();
            }
        }

        return $validationErrors;
    }

    /**
     * Validates the validatorConfiguration of a FieldDefinitionCreateStruct or FieldDefinitionUpdateStruct
     *
     * @param mixed $validatorConfiguration
     *
     * @return \eZ\Publish\SPI\FieldType\ValidationError[]
     */
    public function validateValidatorConfiguration( $validatorConfiguration )
    {
        if ( !is_array( $validatorConfiguration ) )
        {
            return array();
        }

        $validationErrors = array();

        foreach ( $validatorConfiguration as $validatorIdentifier => $constraints )
        {
            if ( in_array( $validatorIdentifier, $this->allowedValidators ) )
            {
                $validator = $this->getValidator( $validatorIdentifier );
                $validationErrors += $validator->validateConstraints( $constraints );
            }
            else
            {
                $validationErrors[] = new ValidationError(
                    "Validator '%validator%' is unknown",
                    null,
                    array(
                        "validator" => $validatorIdentifier
                    )
                );
            }
        }

        return $validationErrors;
    }

    /**
     * Returns an instance of custom validator for NgInteger type
     *
     * @param string $identifier
     *
     * @return \eZ\Publish\Core\FieldType\Validator
     */
    private function getValidator( $identifier )
    {
        $validatorFQN = self::VALIDATOR_NAMESPACE . '\\' . $identifier;

        if ( !isset( $this->validators[$validatorFQN] ) )
        {
            $this->validators[$validatorFQN] = new $validatorFQN;
        }

        return $this->validators[$validatorFQN];
    }
}
