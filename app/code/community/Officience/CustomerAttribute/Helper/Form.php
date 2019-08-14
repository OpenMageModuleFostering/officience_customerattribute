<?php

class Officience_CustomerAttribute_Helper_Form extends Mage_Core_Helper_Abstract {

    public function getListForm($form) {
        if (version_compare(Mage::getVersion(), '1.4.2', '>=')) {
            $customerForm = Mage::getModel('customer/form')
                    ->setFormCode($form);
            $attributes = $customerForm->getAttributes();
        } else {
            $configs = Mage::getModel('customerattribute/officustomerattribute')->getCollection()
                    ->addFieldToFilter('form_code', $form);
            $arrForm = array();
            foreach ($configs->getData() as $valueform) {
                $arrForm[] = $valueform['attribute_code'];
            }
            $attributes = Mage::getResourceModel('customer/attribute_collection')
                    ->setEntityTypeFilter(Mage::getModel('eav/entity')->setType('customer')->getTypeId())
                    ->addVisibleFilter()
                    ->addFieldToFilter('attribute_code', array('in' => $arrForm))
            ;
        }
        return $attributes;
    }

    public function getFormInput() {
        return array(
            'customer_account_create',
            'customer_account_edit'
        );
    }

}

?>
