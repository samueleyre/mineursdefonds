<?php

namespace AmeliaBooking\Infrastructure\WP\InstallActions\DB\Bookable;

use AmeliaBooking\Domain\Common\Exceptions\InvalidArgumentException;
use AmeliaBooking\Domain\ValueObjects\String\Description;
use AmeliaBooking\Domain\ValueObjects\String\Name;
use AmeliaBooking\Infrastructure\WP\InstallActions\DB\AbstractDatabaseTable;

/**
 * Class ExtrasTable
 *
 * @package AmeliaBooking\Infrastructure\WP\InstallActions\DB\Bookable
 */
class ExtrasTable extends AbstractDatabaseTable
{

    const TABLE = 'extras';

    /**
     * @return string
     * @throws InvalidArgumentException
     */
    public static function buildTable()
    {
        $table = self::getTableName();
        $serviceTable = ServicesTable::getTableName();

        $name = Name::MAX_LENGTH;
        $description = Description::MAX_LENGTH;

        return "CREATE TABLE {$table} (
                   `id` int(11) NOT NULL AUTO_INCREMENT,
                   `name` varchar({$name}) NOT NULL default '',
                   `description` text({$description}) NULL,
                   `price` double NOT NULL,
                   `maxQuantity` int(11) NOT NULL,
                   `duration` int(11) NULL,
                   `serviceId` int(11) NOT NULL,
                   `position` int(11) NOT NULL,
                    PRIMARY KEY (`id`),
                    FOREIGN KEY (`serviceId`) REFERENCES {$serviceTable}(`id`) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_general_ci";
    }
}
