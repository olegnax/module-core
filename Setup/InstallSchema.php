<?php
/**
 * @author      Olegnax
 * @package     Olegnax_Core
 * @copyright   Copyright (c) 2021 Olegnax (http://olegnax.com/)
 * @license     https://olegnax.com/license
 */

namespace Olegnax\Core\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{
    /** @noinspection PhpUnusedParameterInspection */

    /**
     * {@inheritdoc}
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $this->_install($setup);
        $setup->endSetup();
    }

    private function _install(SchemaSetupInterface $setup)
    {
        $tables = [
            'adminnotification_inbox' => [
                'notification_id' => [
                    'identity' => true,
                    'nullable' => false,
                    'primary' => true,
                    'unsigned' => true,
                    'comment' => 'Notification id',
                ],
                'severity' => [
                    'type' => Table::TYPE_SMALLINT,
                    'unsigned' => true,
                    'nullable' => false,
                    'identity' => false,
                    'default' => '0',
                    'comment' => 'Problem type',
                ],
                'date_added' => [
                    'type' => Table::TYPE_TIMESTAMP,
                    'nullable' => false,
                    'on_update' => false,
                    'default' => 'CURRENT_TIMESTAMP',
                    'comment' => 'Create date',
                ],
                'title' => [
                    'type' => 'varchar',
                    'size' => 255,
                    'nullable' => false,
                    'comment' => 'Title',
                ],
                'description' => [
                    'type' => Table::TYPE_TEXT,
                    'nullable' => true,
                    'comment' => 'Description',
                ],
                'url' => [
                    'type' => 'varchar',
                    'size' => 255,
                    'nullable' => true,
                    'comment' => 'Url',
                ],
                'is_read' => [
                    'type' => Table::TYPE_SMALLINT,
                    'unsigned' => true,
                    'nullable' => false,
                    'identity' => false,
                    'default' => '0',
                    'comment' => 'Flag if notification read',
                ],
                'is_remove' => [
                    'type' => Table::TYPE_SMALLINT,
                    'unsigned' => true,
                    'nullable' => false,
                    'identity' => false,
                    'default' => '0',
                    'comment' => 'Flag if notification might be removed'
                ],

                'ox_content' => [
                    'type' => Table::TYPE_TEXT,
                    'nullable' => true,
                    'comment' => 'News content',
                ],
                'ox_image' => [
                    'type' => Table::TYPE_TEXT,
                    'nullable' => true,
                    'comment' => 'News image',
                ],
                'ox_validate' => [
                    'type' => Table::TYPE_TEXT,
                    'nullable' => true,
                    'comment' => 'Validate',
                ],
                'ox_type' => [
                    'type' => Table::TYPE_TEXT,
                    'nullable' => true,
                    'comment' => 'Type Content',
                ],
                'date_expire' => [
                    'type' => Table::TYPE_DATETIME,
                    'nullable' => true,
                    'comment' => 'Date Expire',
                ],
                'isOX' => [
                    'type' => Table::TYPE_SMALLINT,
                    'nullable' => false,
                    'default' => 0,
                    'comment' => 'is olegnax news',
                ],
            ],
        ];
        $connection = $setup->getConnection();

        foreach ($tables as $table_name => $table) {
            $exist = $setup->tableExists($table_name);
            if ($exist) {
                $_table = $setup->getTable($table_name);
            } else {
                $_table = $setup->getConnection()->newTable($setup->getTable($table_name));
            }
            foreach ($table as $field_name => $field) {
                foreach ([
                             'type' => Table::TYPE_INTEGER,
                             'size' => null,
                             'comment' => 'Added by plugin',
                         ] as $attr_name => $attr) {
                    if (!array_key_exists($attr_name, $field)) {
                        $field[$attr_name] = $attr;
                    }
                }
                if ($exist) {
                    $connection->addColumn($_table, $field_name, $field);
                } else {
                    $_tableType = $field['type'];
                    $_tableSize = $field['size'];
                    $_tableComment = $field['comment'];
                    unset($field['type'], $field['size'], $field['comment']);
                    $_table->addColumn($field_name, $_tableType, $_tableSize, $field, $_tableComment);
                }
            }
            if (!$exist) {
                $connection->createTable($_table);
            }
        }
    }

}
