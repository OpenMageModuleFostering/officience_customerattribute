<?php

class Officience_CustomerAttribute_Block_Form_Edit extends Mage_Customer_Block_Form_Edit {

    public $AttributeCustomer = '';

    public function getTitle() {
        return $this->__('More Information');
    }

    public function getListAttribute() {
        return Mage::helper('customerattribute/form')->getListForm('customer_account_edit');
    }

    public function renderFieldType() {
        return Mage::helper('customerattribute/field')->renderField(
                        $this->getTypeAttribute()
                        , $this->getAttributeField()
        );
    }

    public function getAttributeField() {
        $itemAttribute = $this->AttributeCustomer;
        $customerInfo = $this->getCustomer()->getData();
        $result = array(
            "name" => $itemAttribute->getAttributeCode(),
            "id" => $itemAttribute->getAttributeCode(),
            "title" => $itemAttribute->getFrontendLabel(),
            "is_required" => $itemAttribute->getIsRequired(),
            "value" => $customerInfo[$itemAttribute->getAttributeCode()],
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

    public function getAttributeCode($itemsAttribute) {
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
