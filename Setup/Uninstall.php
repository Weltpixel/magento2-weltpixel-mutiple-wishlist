<?php
namespace WeltPixel\AdvancedWishlist\Setup;

use Magento\Framework\Setup\UninstallInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

class Uninstall implements UninstallInterface
{
    /**
     * @inheritdoc
     */
    public function uninstall(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $setup->startSetup();

        $connection = $setup->getConnection();
        $tableName = $setup->getTable('wishlist');

        $connection->query(
            "ALTER TABLE `{$tableName}` RENAME INDEX `WISHLIST_CUSTOMER_ID` TO `MULTIPLE_WISHLIST_CUSTOMER_ID`"
        );

        $setup->endSetup();
    }
}

