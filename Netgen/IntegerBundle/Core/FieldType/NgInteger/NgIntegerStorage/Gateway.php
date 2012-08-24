<?php
/**
 * File containing the abstract Gateway class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace Netgen\IntegerBundle\Core\FieldType\NgInteger\NgIntegerStorage;
use eZ\Publish\Core\FieldType\StorageGateway,
    eZ\Publish\SPI\Persistence\Content\VersionInfo,
    eZ\Publish\SPI\Persistence\Content\Field;

abstract class Gateway extends StorageGateway
{
    /**
     * Stores the numbers in the database based on the given field data
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Field $field
     */
    abstract public function storeFieldData( Field $field );

    /**
     * Gets the numbers stored in the field
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Field $field
     */
    abstract public function getFieldData( Field $field );
}
