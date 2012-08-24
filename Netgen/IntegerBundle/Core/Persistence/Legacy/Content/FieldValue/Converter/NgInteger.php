<?php
/**
 * File containing the NgInteger converter
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace Netgen\IntegerBundle\Core\Persistence\Legacy\Content\FieldValue\Converter;
use eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter,
    eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue,
    eZ\Publish\SPI\Persistence\Content\FieldValue,
    eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition,
    eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition;

class NgInteger implements Converter
{
    /**
     * Factory for current class
     *
     * @note Class should instead be configured as service if it gains dependencies.
     *
     * @static
     * @return \Netgen\IntegerBundle\Core\Persistence\Legacy\Content\FieldValue\Converter\NgInteger
     */
    public static function create()
    {
        return new self();
    }

    /**
     * Converts data from $value to $storageFieldValue
     *
     * @param \eZ\Publish\SPI\Persistence\Content\FieldValue $value
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue $storageFieldValue
     */
    public function toStorageValue( FieldValue $value, StorageFieldValue $storageFieldValue )
    {
        $storageFieldValue->dataInt = isset( $value->data["firstNumber"] ) ? $value->data["firstNumber"] : null;
        $storageFieldValue->dataFloat = isset( $value->data["secondNumber"] ) ? $value->data["secondNumber"] : null;
        $storageFieldValue->sortKeyInt = $storageFieldValue->dataInt;
    }

    /**
     * Converts data from $value to $fieldValue
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue $value
     * @param \eZ\Publish\SPI\Persistence\Content\FieldValue $fieldValue
     */
    public function toFieldValue( StorageFieldValue $value, FieldValue $fieldValue )
    {
        $fieldValue->data = array(
            "firstNumber" => $value->dataInt,
            "secondNumber" => $value->dataFloat
        );

        $fieldValue->sortKey = $value->dataInt;
    }

    /**
     * Converts field definition data in $fieldDef into $storageFieldDef
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition $fieldDef
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition $storageDef
     */
    public function toStorageFieldDefinition( FieldDefinition $fieldDef, StorageFieldDefinition $storageDef )
    {
        $validators = $fieldDef->fieldTypeConstraints->validators;

        if ( isset( $validators["NgIntegerValueValidator"]["firstNumberMin"] )
             && is_integer( $validators["NgIntegerValueValidator"]["firstNumberMin"] ) )
        {
            $storageDef->dataInt1 = $validators["NgIntegerValueValidator"]["firstNumberMin"];
        }

        if ( isset( $validators["NgIntegerValueValidator"]["firstNumberMax"] )
             && is_integer( $validators["NgIntegerValueValidator"]["firstNumberMax"] ) )
        {
            $storageDef->dataInt2 = $validators["NgIntegerValueValidator"]["firstNumberMax"];
        }

        if ( isset( $validators["NgIntegerValueValidator"]["secondNumberMin"] )
             && is_integer( $validators["NgIntegerValueValidator"]["secondNumberMin"] ) )
        {
            $storageDef->dataInt3 = $validators["NgIntegerValueValidator"]["secondNumberMin"];
        }

        if ( isset( $validators["NgIntegerValueValidator"]["secondNumberMax"] )
             && is_integer( $validators["NgIntegerValueValidator"]["secondNumberMax"] ) )
        {
            $storageDef->dataInt4 = $validators["NgIntegerValueValidator"]["secondNumberMax"];
        }
    }

    /**
     * Converts field definition data in $storageDef into $fieldDef
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition $storageDef
     * @param \eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition $fieldDef
     */
    public function toFieldDefinition( StorageFieldDefinition $storageDef, FieldDefinition $fieldDef )
    {
        $fieldDef->fieldTypeConstraints->validators = array(
            "NgIntegerValueValidator" => array(
                "firstNumberMin" => is_numeric( $storageDef->dataInt1 ) ? (int) $storageDef->dataInt1 : 0,
                "firstNumberMax" => is_numeric( $storageDef->dataInt2 ) ? (int) $storageDef->dataInt2 : false,
                "secondNumberMin" => is_numeric( $storageDef->dataInt3 ) ? (int) $storageDef->dataInt3 : 0,
                "secondNumberMax" => is_numeric( $storageDef->dataInt4 ) ? (int) $storageDef->dataInt4 : false
            )
        );
    }

    /**
     * Returns the name of the index column in the attribute table
     *
     * Returns the name of the index column the datatype uses, which is either
     * "sort_key_int" or "sort_key_string". This column is then used for
     * filtering and sorting for this type.
     *
     * @return string
     */
    public function getIndexColumn()
    {
        return false;
    }
}
