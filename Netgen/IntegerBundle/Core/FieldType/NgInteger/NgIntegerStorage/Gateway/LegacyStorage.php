<?php
/**
 * File containing the LegacyStorage Gateway class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace Netgen\IntegerBundle\Core\FieldType\NgInteger\NgIntegerStorage\Gateway;
use Netgen\IntegerBundle\Core\FieldType\NgInteger\NgIntegerStorage\Gateway,
    eZ\Publish\Core\Persistence\Legacy\EzcDbHandler,
    eZ\Publish\SPI\Persistence\Content\VersionInfo,
    eZ\Publish\SPI\Persistence\Content\Field,
    RuntimeException;

class LegacyStorage extends Gateway
{
    const NGINTEGER_TABLE = "nginteger";

    /**
     * Connection
     *
     * @var \eZ\Publish\Core\Persistence\Legacy\EzcDbHandler
     */
    protected $dbHandler;

    /**
     * Set database handler for this gateway
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\EzcDbHandler $dbHandler
     * @throws \RuntimeException if $dbHandler is not an instance of
     *         {@link \eZ\Publish\Core\Persistence\Legacy\EzcDbHandler}
     */
    public function setConnection( $dbHandler )
    {
        // This obviously violates the Liskov substitution Principle, but with
        // the given class design there is no sane other option. Actually the
        // dbHandler *should* be passed to the constructor, and there should
        // not be the need to post-inject it.
        if ( !$dbHandler instanceof EzcDbHandler )
        {
            throw new RuntimeException( "Invalid dbHandler passed" );
        }

        $this->dbHandler = $dbHandler;
    }

    /**
     * Returns the active connection
     *
     * @throws \RuntimeException if no connection has been set, yet.
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\EzcDbHandler
     */
    protected function getConnection()
    {
        if ( $this->dbHandler === null )
        {
            throw new RuntimeException( "Missing database connection." );
        }

        return $this->dbHandler;
    }

    /**
     * Stores the numbers in the database based on the given field data
     *
     * @param \eZ\Publish\SPI\Persistence\Content\VersionInfo $versionInfo
     * @param \eZ\Publish\SPI\Persistence\Content\Field $field
     */
    public function storeFieldData( VersionInfo $versionInfo, Field $field )
    {
        if ( $this->hasFieldData( $versionInfo, $field ) )
        {
            $this->updateFieldData( $versionInfo, $field );
        }
        else
        {
            $this->insertFieldData( $versionInfo, $field );
        }
    }

    /**
     * Gets the numbers stored in the field
     *
     * @param \eZ\Publish\SPI\Persistence\Content\VersionInfo $versionInfo
     * @param \eZ\Publish\SPI\Persistence\Content\Field $field
     */
    public function getFieldData( VersionInfo $versionInfo, Field $field )
    {
        $dbHandler = $this->getConnection();

        $query = $dbHandler->createSelectQuery();
        $query->select(
            $dbHandler->quoteColumn( "first_number" ),
            $dbHandler->quoteColumn( "second_number" )
        )->from(
            $dbHandler->quoteTable( self::NGINTEGER_TABLE )
        )->where(
            $query->expr->lAnd(
                $query->expr->eq(
                    $dbHandler->quoteColumn( "contentobject_attribute_id" ),
                    $query->bindValue( $field->id, null, \PDO::PARAM_INT )
                ),
                $query->expr->eq(
                    $dbHandler->quoteColumn( "version" ),
                    $query->bindValue( $versionInfo->versionNo, null, \PDO::PARAM_INT )
                )
            )
        );

        $statement = $query->prepare();
        $statement->execute();

        $rows = $statement->fetchAll( \PDO::FETCH_ASSOC );
        if ( count( $rows ) > 0 )
        {
            $field->value->externalData = array(
                "firstNumber" => (int) $rows[0]["first_number"],
                "secondNumber" => (int) $rows[0]["second_number"]
            );
        }
    }

    /**
     * Deletes field data for all $fieldIds in the version identified by
     * $versionInfo.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\VersionInfo $versionInfo
     * @param array $fieldIds
     */
    public function deleteFieldData( VersionInfo $versionInfo, array $fieldIds )
    {
        if ( empty( $fieldIds ) )
            return;

        $dbHandler = $this->getConnection();

        $query = $dbHandler->createDeleteQuery();
        $query->deleteFrom(
            $dbHandler->quoteTable( self::NGINTEGER_TABLE )
        )->where(
            $query->expr->lAnd(
                $query->expr->in(
                    $dbHandler->quoteColumn( "contentobject_attribute_id" ),
                    $fieldIds
                ),
                $query->expr->eq(
                    $dbHandler->quoteColumn( "version" ),
                    $query->bindValue( $versionInfo->versionNo, null, \PDO::PARAM_INT )
                )
            )
        );

        $query->prepare()->execute();
    }

    /**
     * Returns if the database table has an entry for $field
     *
     * @param \eZ\Publish\SPI\Persistence\Content\VersionInfo $versionInfo
     * @param \eZ\Publish\SPI\Persistence\Content\Field $field
     *
     * @return bool
     */
    private function hasFieldData( VersionInfo $versionInfo, Field $field )
    {
        $dbHandler = $this->getConnection();

        $query = $dbHandler->createSelectQuery();
        $query->select(
            $query->alias( $query->expr->count( "*" ), "count" )
        )->from(
            $dbHandler->quoteTable( self::NGINTEGER_TABLE )
        )->where(
            $query->expr->lAnd(
                $query->expr->eq(
                    $dbHandler->quoteColumn( "contentobject_attribute_id" ),
                    $query->bindValue( $field->id, null, \PDO::PARAM_INT )
                ),
                $query->expr->eq(
                    $dbHandler->quoteColumn( "version" ),
                    $query->bindValue( $versionInfo->versionNo, null, \PDO::PARAM_INT )
                )
            )
        );

        $statement = $query->prepare();
        $statement->execute();

        $count = $statement->fetchColumn();

        return (int) $count > 0;
    }

    /**
     * Inserts $field data to the database table
     *
     * @param \eZ\Publish\SPI\Persistence\Content\VersionInfo $versionInfo
     * @param \eZ\Publish\SPI\Persistence\Content\Field $field
     */
    private function insertFieldData( VersionInfo $versionInfo, Field $field )
    {
        $dbHandler = $this->getConnection();

        $query = $dbHandler->createInsertQuery();
        $query->insertInto(
            $dbHandler->quoteTable( self::NGINTEGER_TABLE )
        )->set(
            $dbHandler->quoteColumn( "contentobject_attribute_id" ),
            $query->bindValue( $field->id, null, \PDO::PARAM_INT )
        )->set(
            $dbHandler->quoteColumn( "version" ),
            $query->bindValue( $versionInfo->versionNo, null, \PDO::PARAM_INT )
        )->set(
            $dbHandler->quoteColumn( "first_number" ),
            $query->bindValue( $field->value->externalData["firstNumber"], null, \PDO::PARAM_INT )
        )->set(
            $dbHandler->quoteColumn( "second_number" ),
            $query->bindValue( $field->value->externalData["secondNumber"], null, \PDO::PARAM_INT )
        );

        $query->prepare()->execute();
    }

    /**
     * Updates $field data in the database table
     *
     * @param \eZ\Publish\SPI\Persistence\Content\VersionInfo $versionInfo
     * @param \eZ\Publish\SPI\Persistence\Content\Field $field
     */
    private function updateFieldData( VersionInfo $versionInfo, Field $field )
    {
        $dbHandler = $this->getConnection();

        $query = $dbHandler->createUpdateQuery();
        $query->update(
            $dbHandler->quoteTable( self::NGINTEGER_TABLE )
        )->set(
            $dbHandler->quoteColumn( "first_number" ),
            $query->bindValue( $field->value->externalData["firstNumber"], null, \PDO::PARAM_INT )
        )->set(
            $dbHandler->quoteColumn( "second_number" ),
            $query->bindValue( $field->value->externalData["secondNumber"], null, \PDO::PARAM_INT )
        )->where(
            $query->expr->lAnd(
                $query->expr->eq(
                    $dbHandler->quoteColumn( "contentobject_attribute_id" ),
                    $query->bindValue( $field->id, null, \PDO::PARAM_INT )
                ),
                $query->expr->eq(
                    $dbHandler->quoteColumn( "version" ),
                    $query->bindValue( $versionInfo->versionNo, null, \PDO::PARAM_INT )
                )
            )
        );

        $query->prepare()->execute();
    }
}
