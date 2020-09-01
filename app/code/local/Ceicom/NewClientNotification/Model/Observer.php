<?php
    class Ceicom_NewClientNotification_Model_Observer {
        public function customerSaveAfter(Varien_Event_Observer $observer) {
            // configs
            $enabled = Mage::getStoreConfig('newclientnotification/config/enabled');
            $email = Mage::getStoreConfig('newclientnotification/config/email');
            $email_name = Mage::getStoreConfig('newclientnotification/config/email_name');
            $email_subject = Mage::getStoreConfig('newclientnotification/config/email_subject');

            if ($enabled != 1) { 
                return false; 
            }
            
            // format date
            setlocale(LC_TIME, 'pt_BR', 'pt_BR.utf-8', 'pt_BR.utf-8', 'portuguese');
            date_default_timezone_set('America/Sao_Paulo');

            // client name
            $customer = $observer->getEvent()->getCustomer();
            $customerName = $customer->getFirstname() . ' ' . $customer->getLastname();

            // customer e-mail
            $emailTemplate = Mage::getModel('core/email_template')->loadDefault('new_client_notification_email_template');
            $senderName = $email_name;
            $subject = $email_subject;
            $recipientEmail = $email;
            $senderEmail = Mage::getStoreConfig('contacts/email/recipient_email');
            
            // return info template e-mail
            $emailTemplateVariables = array();
            $emailTemplateVariables['username'] = $customerName;
            $emailTemplateVariables['date'] = date('d/m/Y, g:i a');

            // template e-mail
            $processedTemplate = $emailTemplate->getProcessedTemplate($emailTemplateVariables);
            
            // send
            $mail = Mage::getModel('core/email')
                    ->setBody($processedTemplate)
                    ->setSubject($subject)
                    ->setFromEmail($senderEmail)
                    ->setFromName($senderName)
                    ->setType('html')
                    ->setToEmail($recipientEmail);

            try {
                $mail->send();
            } catch(Exception $error) {
                Mage::getSingleton('core/session')->addError($error->getMessage());
                return false;
            }
        }
    }