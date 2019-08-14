<?php

class Officience_CustomerAttribute_Helper_Field {

    public function getFieldDeFault() {
        return array('firstname',
            'lastname',
            'group_id',
            'created_in',
            'customer_work',
            'website_id',
            'email',
            'prefix',
            'middlename',
            'taxvat',
            'suffix',
            'dob',
            'gender',
            'created_at');
    }

    public function renderField($type, $attributes) {
        $html = '';
        if (in_array($type, array('boolean', 'text', 'date'))) {
            $types = '';
            if ($type == 'date') {
                $types = 'text';
            } else {
                $types = $type;
            }
            $html.= '<input type="' . $types . '" ';
            $html .= $this->setAttributeField($type, $attributes);
            if ($type == 'date') {
                $html .= ' style="width:120px !important;" ';
            }
            $html .= $this->classRequireField($type, $attributes);
            $html .=' />';
            if ($type == 'date') {
                $idDate = ($attributes['id'] ? $attributes['id'] . '_trig' : '');
                $html .= '<img id="' . $idDate . '" class="v-middle" style="" title="Select Date" alt="" src="' . (Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN) . 'frontend/base/default/images/calendar.gif') . '">';
                $html .='<script type="text/javascript">
                    Calendar.setup({
                    inputField: "' . $attributes['id'] . '",
                    ifFormat: "' . Varien_Date::convertZendToStrFtime(Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT), true, false) . '",
                    showsTime: false,
                    button: "' . $idDate . '",
                    align: "Bl",
                    singleClick : true
                    });

</script>';
            }
        } elseif (in_array($type, array('select', 'multiselect'))) {
            $html .= '<select';
            if ($type == 'multiselect') {
                $html .= ' multiple="multiple" ';
            }
            $html .= $this->setAttributeField($type, $attributes);
            $html .= $this->classRequireField($type, $attributes);
            $html .=" >";
            $html .=$this->setValueFieldSelect($type, $attributes);
            $html .= '</select>';
        } elseif ($type == 'textarea') {
            $html .='<textarea';
            $html .= $this->setAttributeField($type, $attributes);
            $html .= $this->classRequireField($type, $attributes);
            $html .= '>';
            $html .=$attributes['value'];
            $html .='</textarea>';
        }
        return $html;
    }

    public function classRequireField($type, $attributes) {
        $class = ' class = " ';
        if ($type == 'text') {
            $class .= ' input-text ';
        }
        $class .= ($attributes['is_required'] ? 'required-entry' : " ");
        $class .= ' ' . ($attributes['frontend_class'] ? $attributes['frontend_class'] : ' ');
        $class .= ' "';
        return $class;
    }

    public function setValueFieldSelect($type, $attributes) {
        $html = '';
        if ($type == 'multiselect') {
            $arrValue = explode(',', $attributes['value']);
            foreach ($attributes['values'] as $value) {
                if ($value['label']) {
                    $html .='<option ';
                    if (in_array($value['value'], $arrValue)) {
                        $html.=' selected="selected" ';
                    }
                    $html .=' value="' . (($value['value'] != '') ? $value['value'] : '0') . '">';
                    $html .= $value['label'];
                    $html .= '</option>';
                }
            }
        } else {
            foreach ($attributes['values'] as $value) {
                if ($value['label']) {
                    $html .='<option ';
                    if ($attributes['value'] == $value['value']) {
                        $html.=' selected="selected" ';
                    }
                    $html .=' value="' . (($value['value'] != '') ? $value['value'] : '0') . '">';
                    $html .= $value['label'];
                    $html .= '</option>';
                }
            }
        }
        return $html;
    }

    public function setAttributeField($type, $attributes) {
        $html = '';
        foreach ($attributes as $key => $attribute) {
            if (!in_array($key, array('frontend_class', 'values', 'is_required'))) {
                if (in_array($type, array('textarea', 'select', 'multiselect')) && in_array($key, array('value'))) {

                    continue;
                }

                $html.= ' ' . $key . '="' . ($attribute ? $attribute : '') . '" ';
            }
        }
        return $html;
    }

}

?>
