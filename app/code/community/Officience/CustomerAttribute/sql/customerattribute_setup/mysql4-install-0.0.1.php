<?php

if (version_compare(Mage::getVersion(), '1.4.2', '<')) {
    $installer = $this;
    $installer->startSetup();
    $query = "";
    $query .= "
        CREATE TABLE IF NOT EXISTS {$this->getTable('officustomerattribute')}   
        (
            `form_code` varchar(255) ,
            `attribute_code` varchar(255),
            PRIMARY KEY (`form_code`,`attribute_code`)
        )ENGINE= InnoDB DEFAULT CHARSET = utf8;";
    $fieldset = Mage::getConfig()->getFieldset('customer_account');
    if (isset($fieldset)) {
        foreach ($fieldset as $key => $value) {
            if ($key) {
                if ($value->is('update')) {
                    $query .= "Insert INTO {$this->getTable('officustomerattribute')} (`form_code`,`attribute_code`)
                VALUES ('customer_account_edit','" . $key . "');";
                }
                if ($value->is('create')) {
                    $query .= "Insert INTO {$this->getTable('officustomerattribute')} (`form_code`,`attribute_code`)
                VALUES ('customer_account_create','" . $key . "');";
                }
            }
        }
    }
    $installer->run($query);
    $installer->endSetup();
}
?>