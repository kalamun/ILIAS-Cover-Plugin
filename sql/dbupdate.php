<#1>
<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE  */

/**
 * Test Page Component  plugin: database update script
 */ 

/**
 * Additional values
 */
if(!$ilDB->tableExists('pctcp_data'))
{
    $fields = array(
        'id' => array(
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        ),

        'data' => array(
            'type' => 'text',
            'length' => 255,
            'notnull' => false,
        ),
    );
    $ilDB->createTable('pctcp_data', $fields);
    $ilDB->addPrimaryKey('pctcp_data', array('id'));
    $ilDB->createSequence('pctcp_data');
}
?>