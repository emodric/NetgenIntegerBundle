<?php
/**
 * File containing the NgInteger Value class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace Netgen\IntegerBundle\Core\FieldType\NgInteger;
use eZ\Publish\Core\FieldType\Value as BaseValue;

/**
 * Value for NgInteger field type
 */
class Value extends BaseValue
{
    /**
     * First number
     *
     * @var int
     */
    public $firstNumber = 0;

    /**
     * Second number
     *
     * @var int
     */
    public $secondNumber = 0;

    /**
     * Construct a new Value object and initialize it with the numbers
     *
     * @param int $firstNumber
     * @param int $secondNumber
     */
    public function __construct( $firstNumber = null, $secondNumber = null )
    {
        if ( $firstNumber !== null )
            $this->firstNumber = $firstNumber;

        if ( $secondNumber !== null )
            $this->secondNumber = $secondNumber;
    }

    /**
     * Returns a string representation of the field value.
     * This string representation must be compatible with format accepted via
     * {@link \eZ\Publish\SPI\FieldType\FieldType::buildValue}
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->firstNumber . ', ' . (string) $this->secondNumber;
    }

    /**
     * Returns the title of the current field value.
     * It will be used to generate content name and url alias if current field is designated
     * to be used in the content name/urlAlias pattern.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->__toString();
    }
}
