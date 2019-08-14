<?php

class Officience_CustomerAttribute_Model_Mysql4_Officustomerattribute extends Mage_Core_Model_Mysql4_Abstract {

    public function _construct() {
        $this->_init('customerattribute/officustomerattribute', array('form_code','attribute_id'));
    }

}

?>
