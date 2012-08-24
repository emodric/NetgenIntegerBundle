<?php
/**
 * File containing the NgIntegerValueValidator class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace Netgen\IntegerBundle\Core\FieldType\Validator;
use eZ\Publish\Core\FieldType\Validator,
    eZ\Publish\Core\FieldType\ValidationError,
    eZ\Publish\Core\FieldType\Value;

/**
 * Validate ranges of two integer values
 *
 * @property int $firstNumberMin The minimum allowed integer value for first number
 * @property int $firstNumberMax The maximum allowed integer value for first number
 * @property int $secondNumberMin The minimum allowed integer value for second number
 * @property int $secondNumberMax The maximum allowed integer value for second number
 */
class NgIntegerValueValidator extends Validator
{
    protected $constraints = array(
        "firstNumberMin" => false,
        "firstNumberMax" => false,
        "secondNumberMin" => false,
        "secondNumberMax" => false
    );

    protected $constraintsSchema = array(
        "firstNumberMin" => array(
            "type" => "int",
            "default" => 0
        ),
        "firstNumberMax" => array(
            "type" => "int",
            "default" => false
        ),
        "secondNumberMin" => array(
            "type" => "int",
            "default" => 0
        ),
        "secondNumberMax" => array(
            "type" => "int",
            "default" => false
        )
    );

    public function validateConstraints( $constraints )
    {
        $validationErrors = array();

        if ( isset( $constraints["firstNumberMin"] ) && isset( $constraints["firstNumberMax"] ) &&
             is_integer( $constraints["firstNumberMin"] ) && is_integer( $constraints["firstNumberMax"] ) )
        {
            if ( $constraints["firstNumberMin"] > $constraints["firstNumberMax"] )
            {
                $validationErrors[] = new ValidationError(
                    "Validator parameter firstNumberMin cannot be larger than firstNumberMax parameter"
                );
            }
        }

        if ( isset( $constraints["secondNumberMin"] ) && isset( $constraints["secondNumberMax"] ) &&
             is_integer( $constraints["secondNumberMin"] ) && is_integer( $constraints["secondNumberMax"] ) )
        {
            if ( $constraints["secondNumberMin"] > $constraints["secondNumberMax"] )
            {
                $validationErrors[] = new ValidationError(
                    "Validator parameter secondNumberMin cannot be larger than secondNumberMax parameter"
                );
            }
        }

        foreach ( $constraints as $name => $value )
        {
            switch ( $name )
            {
                case "firstNumberMin":
                case "firstNumberMax":
                case "secondNumberMin":
                case "secondNumberMax":
                {
                    if ( $value !== false && !is_integer( $value ) )
                    {
                        $validationErrors[] = new ValidationError(
                            "Validator parameter '%parameter%' value must be of integer type",
                            null,
                            array(
                                "parameter" => $name
                            )
                        );
                    }
                } break;
                default:
                {
                    $validationErrors[] = new ValidationError(
                        "Validator parameter '%parameter%' is unknown",
                        null,
                        array(
                            "parameter" => $name
                        )
                    );
                }
            }
        }

        return $validationErrors;
    }

    /**
     * Perform validation on $value.
     *
     * Will return true when all constraints are matched. If one or more
     * constraints fail, the method will return false.
     *
     * When a check against a constraint has failed, an entry will be added to the
     * $errors array.
     *
     * @param \eZ\Publish\Core\FieldType\Value $value
     *
     * @return bool
     */
    public function validate( Value $value )
    {
        $isValid = true;

        if ( $this->constraints["firstNumberMin"] !== false && $value->firstNumber < $this->constraints["firstNumberMin"] )
        {
            $this->errors[] = new ValidationError(
                "The value of first number can not be smaller than %size%.",
                null,
                array(
                    "size" => $this->constraints["firstNumberMin"]
                )
            );

            $isValid = false;
        }

        if ( $this->constraints["firstNumberMax"] !== false && $value->firstNumber > $this->constraints["firstNumberMax"] )
        {
            $this->errors[] = new ValidationError(
                "The value of first number can not be higher than %size%.",
                null,
                array(
                    "size" => $this->constraints["firstNumberMax"]
                )
            );

            $isValid = false;
        }

        if ( $this->constraints["secondNumberMin"] !== false && $value->secondNumber < $this->constraints["secondNumberMin"] )
        {
            $this->errors[] = new ValidationError(
                "The value of second number can not be smaller than %size%.",
                null,
                array(
                    "size" => $this->constraints["secondNumberMin"]
                )
            );

            $isValid = false;
        }

        if ( $this->constraints["secondNumberMax"] !== false && $value->secondNumber > $this->constraints["secondNumberMax"] )
        {
            $this->errors[] = new ValidationError(
                "The value of second number can not be higher than %size%.",
                null,
                array(
                    "size" => $this->constraints["secondNumberMax"]
                )
            );

            $isValid = false;
        }

        return $isValid;
    }
}
