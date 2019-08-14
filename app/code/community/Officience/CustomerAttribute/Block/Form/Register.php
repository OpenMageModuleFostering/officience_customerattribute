<?php

class Officience_CustomerAttribute_Block_Form_Register extends Mage_Customer_Block_Form_Register {

    public $AttributeCustomer = '';

    public function getListAttribute() {
        return Mage::helper('customerattribute/form')->getListForm('customer_account_create');
    }

    public function renderFieldType() {
        return Mage::helper('customerattribute/field')->renderField(
                        $this->getTypeAttribute()
                        , $this->getAttributeField()
        );
    }

    public function getTitle() {
        return $this->__('Account Infomation');
    }

    public function getAttributeField() {
        $itemAttribute = $this->AttributeCustomer;
        $result = array(
            "name" => $itemAttribute->getAttributeCode(),
            "id" => $itemAttribute->getAttributeCode(),
            "title" => $itemAttribute->getFrontendLabel(),
            "is_required" => $itemAttribute->getIsRequired(),
            "value" => ($itemAttribute->getDefaultValue()?$itemAttribute->getDefaultValue():' '),
            "frontend_class" => $itemAttribute->getFrontendClass(),
        );
        if (in_array($this->getTypeAttribute(), array('select', 'multiselect'))) {
            $result['values'] = $itemAttribute->getSource()->getAllOptions();
        }
        return $result;
    }

    public function getFieldDefault() {
        return Mage::Helper('customerattribute/field')->getFieldDeFault();
    }

    public function getAttributeCode() {
        return $this->AttributeCustomer->getAttributeCode();
    }

    public function setAttribute($attribute) {
        $this->AttributeCustomer = $attribute;
    }

    public function getTypeAttribute() {
        return $this->AttributeCustomer->getFrontendInput();
    }

    public function getLabelAttribute() {
        return $this->AttributeCustomer->getFrontendLabel();
    }

}

?>
