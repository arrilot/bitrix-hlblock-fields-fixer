<?php

namespace Arrilot\BitrixHLBlockFieldsFixer;

use Bitrix\Highloadblock\HighloadBlockTable;
use Bitrix\Main\Application;

class Fixer
{
    /**
     * @var array
     */
    protected static $newFieldTypes = [
        'string' => 'varchar(255)',
        'string_formatted' => 'varchar(255)',
        'text' => 'text',
        'boolean' => 'tinyint(1)',
    ];
    
    /**
     * @param $field
     * @param $type
     */
    public static function setNewFieldType($field , $type)
    {
        static::$newFieldTypes[$field] = $type;
    }
    
    /**
     * @param $field
     * @return mixed|null
     */
    public static function getNewFieldType($field)
    {
        return isset(static::$newFieldTypes[$field]) ? static::$newFieldTypes[$field] : null;
    }
    
    /**
     * Main handler.
     *
     * @param $field
     * @return bool
     */
    public static function adjustFieldInDatabaseOnAfterUserTypeAdd($field)
    {
        if (!preg_match('/^HLBLOCK_(\d+)$/', $field['ENTITY_ID'], $matches)) {
            return true;
        }
        
        // множественные не трогаем
        if ($field["MULTIPLE"] === 'Y') {
            return true;
        }
        
        $connection = Application::getConnection();
        $sqlHelper = $connection->getSqlHelper();
        
        $hlblock_id = $matches[1];
        $hlblock = HighloadBlockTable::getById($hlblock_id)->fetch();
        if (empty($hlblock)) {
            return true;
        }
        
        $settings = unserialize($field['SETTINGS']);
        $sqlTableName = $sqlHelper->quote($hlblock['TABLE_NAME']);
        $sqlFieldName = $sqlHelper->quote($field['FIELD_NAME']);
        
        $type = $field["USER_TYPE_ID"];
        if (($type === 'string' || $type === 'string_formatted') && $settings['ROWS'] > 1) {
            $type = 'text';
        }
        
        $newType = static::getNewFieldType($type);
        if ($newType) {
            $connection->query(sprintf('ALTER TABLE %s MODIFY COLUMN %s %s', $sqlTableName, $sqlFieldName, $newType));
        }
        
        return true;
    }
}
