<?php

require_once 'Mage/Customer/controllers/AccountController.php';

class Officience_CustomerAttribute_AccountController extends Mage_Customer_AccountController {

    public function createPostAction() {
        $session = $this->_getSession();
        if ($session->isLoggedIn()) {
            $this->_redirect('*/*/');
            return;
        }
        $session->setEscapeMessages(true); // prevent XSS injection in user input

        if (version_compare(Mage::getVersion(), '1.4.2', '>=')) {

            if ($this->getRequest()->isPost()) {
                $errors = array();

                if (!$customer = Mage::registry('current_customer')) {
                    $customer = Mage::getModel('customer/customer')->setId(null);
                }

                /* @var $customerForm Mage_Customer_Model_Form */
                $customerForm = Mage::getModel('customer/form');
                $customerForm->setFormCode('customer_account_create')
                        ->setEntity($customer);

                $customerData = $customerForm->extractData($this->getRequest());

                if ($this->getRequest()->getParam('is_subscribed', false)) {
                    $customer->setIsSubscribed(1);
                }

                /**
                 * Initialize customer group id
                 */
                $customer->getGroupId();

                if ($this->getRequest()->getPost('create_address')) {
                    /* @var $address Mage_Customer_Model_Address */
                    $address = Mage::getModel('customer/address');
                    /* @var $addressForm Mage_Customer_Model_Form */
                    $addressForm = Mage::getModel('customer/form');
                    $addressForm->setFormCode('customer_register_address')
                            ->setEntity($address);

                    $addressData = $addressForm->extractData($this->getRequest(), 'address', false);
                    $addressErrors = $addressForm->validateData($addressData);
                    if ($addressErrors === true) {
                        $address->setId(null)
                                ->setIsDefaultBilling($this->getRequest()->getParam('default_billing', false))
                                ->setIsDefaultShipping($this->getRequest()->getParam('default_shipping', false));
                        $addressForm->compactData($addressData);
                        $customer->addAddress($address);

                        $addressErrors = $address->validate();
                        if (is_array($addressErrors)) {
                            $errors = array_merge($errors, $addressErrors);
                        }
                    } else {
                        $errors = array_merge($errors, $addressErrors);
                    }
                }

                try {
                    $customerErrors = $customerForm->validateData($customerData);
                    if ($customerErrors !== true) {
                        $errors = array_merge($customerErrors, $errors);
                    } else {
                        $customerForm->compactData($customerData);
                        $customer->setPassword($this->getRequest()->getPost('password'));
                        $customer->setConfirmation($this->getRequest()->getPost('confirmation'));
                        $customerErrors = $customer->validate();
                        if (is_array($customerErrors)) {
                            $errors = array_merge($customerErrors, $errors);
                        }
                    }

                    $validationResult = count($errors) == 0;

                    if (true === $validationResult) {
                        $customer->save();

                        if ($customer->isConfirmationRequired()) {
                            $customer->sendNewAccountEmail('confirmation', $session->getBeforeAuthUrl());
                            $session->addSuccess($this->__('Account confirmation is required. Please, check your email for the confirmation link. To resend the confirmation email please <a href="%s">click here</a>.', Mage::helper('customer')->getEmailConfirmationUrl($customer->getEmail())));
                            $this->_redirectSuccess(Mage::getUrl('*/*/index', array('_secure' => true)));
                            return;
                        } else {
                            $session->setCustomerAsLoggedIn($customer);
                            $url = $this->_welcomeCustomer($customer);
                            $this->_redirectSuccess($url);
                            return;
                        }
                    } else {
                        $session->setCustomerFormData($this->getRequest()->getPost());
                        if (is_array($errors)) {
                            foreach ($errors as $errorMessage) {
                                $session->addError($errorMessage);
                            }
                        } else {
                            $session->addError($this->__('Invalid customer data'));
                        }
                    }
                } catch (Mage_Core_Exception $e) {
                    $session->setCustomerFormData($this->getRequest()->getPost());
                    if ($e->getCode() === Mage_Customer_Model_Customer::EXCEPTION_EMAIL_EXISTS) {
                        $url = Mage::getUrl('customer/account/forgotpassword');
                        $message = $this->__('There is already an account with this email address. If you are sure that it is your email address, <a href="%s">click here</a> to get your password and access your account.', $url);
                        $session->setEscapeMessages(false);
                    } else {
                        $message = $e->getMessage();
                    }
                    $session->addError($message);
                } catch (Exception $e) {
                    $session->setCustomerFormData($this->getRequest()->getPost())
                            ->addException($e, $this->__('Cannot save the customer.'));
                }
            }

            $this->_redirectError(Mage::getUrl('*/*/create', array('_secure' => true)));
        } else {
            if ($this->getRequest()->isPost()) {
                $errors = array();

                if (!$customer = Mage::registry('current_customer')) {
                    $customer = Mage::getModel('customer/customer')->setId(null);
                }

                $data = $this->_filterPostData($this->getRequest()->getPost());
                $fields = Mage::getModel('customerattribute/officustomerattribute')->getCollection()
                        ->addFieldToFilter('form_code', 'customer_account_create')
                        ->getData();
                foreach ($fields as $node) {
                    $attributeCode = $node['attribute_code'];
                    if (isset($data[$attributeCode])) {
                        if ($attributeCode == 'email') {
                            $data[$attributeCode] = trim($data[$attributeCode]);
                        }
                        $customer->setData($attributeCode, $data[$attributeCode]);
                    }
                }

                if ($this->getRequest()->getParam('is_subscribed', false)) {
                    $customer->setIsSubscribed(1);
                }

                /**
                 * Initialize customer group id
                 */
                $customer->getGroupId();

                if ($this->getRequest()->getPost('create_address')) {
                    $address = Mage::getModel('customer/address')
                            ->setData($this->getRequest()->getPost())
                            ->setIsDefaultBilling($this->getRequest()->getParam('default_billing', false))
                            ->setIsDefaultShipping($this->getRequest()->getParam('default_shipping', false))
                            ->setId(null);
                    $customer->addAddress($address);

                    $errors = $address->validate();
                    if (!is_array($errors)) {
                        $errors = array();
                    }
                }

                try {
                    $validationCustomer = $customer->validate();
                    if (is_array($validationCustomer)) {
                        $errors = array_merge($validationCustomer, $errors);
                    }
                    $validationResult = count($errors) == 0;

                    if (true === $validationResult) {
                        $customer->save();

                        if ($customer->isConfirmationRequired()) {
                            $customer->sendNewAccountEmail('confirmation', $session->getBeforeAuthUrl());
                            $session->addSuccess($this->__('Account confirmation is required. Please, check your email for the confirmation link. To resend the confirmation email please <a href="%s">click here</a>.', Mage::helper('customer')->getEmailConfirmationUrl($customer->getEmail())));
                            $this->_redirectSuccess(Mage::getUrl('*/*/index', array('_secure' => true)));
                            return;
                        } else {
                            $session->setCustomerAsLoggedIn($customer);
                            $url = $this->_welcomeCustomer($customer);
                            $this->_redirectSuccess($url);
                            return;
                        }
                    } else {
                        $session->setCustomerFormData($this->getRequest()->getPost());
                        if (is_array($errors)) {
                            foreach ($errors as $errorMessage) {
                                $session->addError($errorMessage);
                            }
                        } else {
                            $session->addError($this->__('Invalid customer data'));
                        }
                    }
                } catch (Mage_Core_Exception $e) {
                    $session->setCustomerFormData($this->getRequest()->getPost());
                    if ($e->getCode() === Mage_Customer_Model_Customer::EXCEPTION_EMAIL_EXISTS) {
                        $url = Mage::getUrl('customer/account/forgotpassword');
                        $message = $this->__('There is already an account with this email address. If you are sure that it is your email address, <a href="%s">click here</a> to get your password and access your account.', $url);
                        $session->setEscapeMessages(false);
                    } else {
                        $message = $e->getMessage();
                    }
                    $session->addError($message);
                } catch (Exception $e) {
                    $session->setCustomerFormData($this->getRequest()->getPost())
                            ->addException($e, $this->__('Cannot save the customer.'));
                }
            }
            $this->_redirectError(Mage::getUrl('*/*/create', array('_secure' => true)));
        }
    }

    public function editPostAction() {
        if (!$this->_validateFormKey()) {
            return $this->_redirect('*/*/edit');
        }
        if (version_compare(Mage::getVersion(), '1.4.2', '>=')) {

            if ($this->getRequest()->isPost()) {
                /** @var $customer Mage_Customer_Model_Customer */
                $customer = $this->_getSession()->getCustomer();

                /** @var $customerForm Mage_Customer_Model_Form */
                $customerForm = Mage::getModel('customer/form');
                $customerForm->setFormCode('customer_account_edit')
                        ->setEntity($customer);

                $customerData = $customerForm->extractData($this->getRequest());

                $errors = array();
                $customerErrors = $customerForm->validateData($customerData);
                if ($customerErrors !== true) {
                    $errors = array_merge($customerErrors, $errors);
                } else {
                    $customerForm->compactData($customerData);
                    $errors = array();

                    // If password change was requested then add it to common validation scheme
                    if ($this->getRequest()->getParam('change_password')) {
                        $currPass = $this->getRequest()->getPost('current_password');
                        $newPass = $this->getRequest()->getPost('password');
                        $confPass = $this->getRequest()->getPost('confirmation');

                        $oldPass = $this->_getSession()->getCustomer()->getPasswordHash();
                        if (Mage::helper('core/string')->strpos($oldPass, ':')) {
                            list($_salt, $salt) = explode(':', $oldPass);
                        } else {
                            $salt = false;
                        }

                        if ($customer->hashPassword($currPass, $salt) == $oldPass) {
                            if (strlen($newPass)) {
                                /**
                                 * Set entered password and its confirmation - they
                                 * will be validated later to match each other and be of right length
                                 */
                                $customer->setPassword($newPass);
                                $customer->setConfirmation($confPass);
                            } else {
                                $errors[] = $this->__('New password field cannot be empty.');
                            }
                        } else {
                            $errors[] = $this->__('Invalid current password');
                        }
                    }

                    // Validate account and compose list of errors if any
                    $customerErrors = $customer->validate();
                    if (is_array($customerErrors)) {
                        $errors = array_merge($errors, $customerErrors);
                    }
                }

                if (!empty($errors)) {
                    $this->_getSession()->setCustomerFormData($this->getRequest()->getPost());
                    foreach ($errors as $message) {
                        $this->_getSession()->addError($message);
                    }
                    $this->_redirect('*/*/edit');
                    return $this;
                }

                try {
                    $customer->setConfirmation(null);
                    $customer->save();
                    $this->_getSession()->setCustomer($customer)
                            ->addSuccess($this->__('The account information has been saved.'));

                    $this->_redirect('customer/account');
                    return;
                } catch (Mage_Core_Exception $e) {
                    $this->_getSession()->setCustomerFormData($this->getRequest()->getPost())
                            ->addError($e->getMessage());
                } catch (Exception $e) {
                    $this->_getSession()->setCustomerFormData($this->getRequest()->getPost())
                            ->addException($e, $this->__('Cannot save the customer.'));
                }
            }

            $this->_redirect('*/*/edit');
        } else {


            if ($this->getRequest()->isPost()) {
                $customer = Mage::getModel('customer/customer')
                        ->setId($this->_getSession()->getCustomerId())
                        ->setWebsiteId($this->_getSession()->getCustomer()->getWebsiteId());


                $data = $this->_filterPostData($this->getRequest()->getPost());
                $fields = Mage::getModel('customerattribute/officustomerattribute')->getCollection()
                        ->addFieldToFilter('form_code', 'customer_account_edit')
                        ->getData();
                foreach ($fields as $node) {
                    $attributeCode = $node['attribute_code'];
                    if (isset($data[$attributeCode])) {
                        if ($attributeCode == 'email') {
                            $data[$attributeCode] = trim($data[$attributeCode]);
                        }
                        $customer->setData($attributeCode, $data[$attributeCode]);
                    }
                }

                $errors = $customer->validate();
                if (!is_array($errors)) {
                    $errors = array();
                }

                /**
                 * we would like to preserver the existing group id
                 */
                if ($this->_getSession()->getCustomerGroupId()) {
                    $customer->setGroupId($this->_getSession()->getCustomerGroupId());
                }

                if ($this->getRequest()->getParam('change_password')) {
                    $currPass = $this->getRequest()->getPost('current_password');
                    $newPass = $this->getRequest()->getPost('password');
                    $confPass = $this->getRequest()->getPost('confirmation');

                    if (empty($currPass) || empty($newPass) || empty($confPass)) {
                        $errors[] = $this->__('The password fields cannot be empty.');
                    }

                    if ($newPass != $confPass) {
                        $errors[] = $this->__('Please make sure your passwords match.');
                    }

                    $oldPass = $this->_getSession()->getCustomer()->getPasswordHash();
                    if (strpos($oldPass, ':')) {
                        list($_salt, $salt) = explode(':', $oldPass);
                    } else {
                        $salt = false;
                    }

                    if ($customer->hashPassword($currPass, $salt) == $oldPass) {
                        $customer->setPassword($newPass);
                    } else {
                        $errors[] = $this->__('Invalid current password');
                    }
                }

                if (!empty($errors)) {
                    $this->_getSession()->setCustomerFormData($this->getRequest()->getPost());
                    foreach ($errors as $message) {
                        $this->_getSession()->addError($message);
                    }
                    $this->_redirect('*/*/edit');
                    return $this;
                }

                try {
                    $customer->save();
                    $this->_getSession()->setCustomer($customer)
                            ->addSuccess($this->__('The account information has been saved.'));

                    $this->_redirect('customer/account');
                    return;
                } catch (Mage_Core_Exception $e) {
                    $this->_getSession()->setCustomerFormData($this->getRequest()->getPost())
                            ->addError($e->getMessage());
                } catch (Exception $e) {
                    $this->_getSession()->setCustomerFormData($this->getRequest()->getPost())
                            ->addException($e, $this->__('Cannot save the customer.'));
                }
            }

            $this->_redirect('*/*/edit');
        }
    }

}
