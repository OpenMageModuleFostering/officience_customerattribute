<?php
class Officience_CustomerAttribute_Model_Mysql4_Officustomerattribute_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct() {
        parent::_construct();        
        $this->_init('customerattribute/officustomerattribute');
    }
}
?>
