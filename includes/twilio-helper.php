<?php
// includes/twilio-helper.php - Fixed Twilio SMS helper functions
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/twilio.php';

use Twilio\Rest\Client;
use Twilio\Exceptions\TwilioException;

class TwilioSMSHelper {
    private $client;
    
    public function __construct() {
        try {
            $this->client = new Client(TwilioConfig::ACCOUNT_SID, TwilioConfig::AUTH_TOKEN);
        } catch (Exception $e) {
            echo "Failed to initialize Twilio client: " . $e->getMessage() . "\n";
            throw $e;
        }
    }
    
    public function sendSMS($to, $message) {
        try {
            // Validate phone number format
            if (!$this->isValidPhoneNumber($to)) {
                throw new Exception("Invalid phone number format: $to");
            }
            
            // Validate message length
            if (strlen($message) > 1600) {
                throw new Exception("Message too long. Maximum 1600 characters allowed.");
            }
            
            echo "Sending SMS to $to with message: " . substr($message, 0, 50) . "...\n";
            
            $sms = $this->client->messages->create(
                $to,
                [
                    'from' => TwilioConfig::FROM_NUMBER,
                    'body' => $message
                ]
            );
            
            return [
                'success' => true,
                'message_sid' => $sms->sid,
                'status' => $sms->status
            ];
        } catch (TwilioException $e) {
            return [
                'success' => false,
                'error' => 'Twilio Error: ' . $e->getMessage(),
                'code' => $e->getCode()
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    private function isValidPhoneNumber($phone) {
        // Check if phone number starts with + and contains only digits after
        return preg_match('/^\+[1-9]\d{1,14}$/', $phone);
    }
    
    // Test connection method
    public function testConnection() {
        try {
            // Try to get account info to test connection
            $account = $this->client->api->accounts(TwilioConfig::ACCOUNT_SID)->fetch();
            return [
                'success' => true,
                'account_name' => $account->friendlyName,
                'status' => $account->status
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}
?>