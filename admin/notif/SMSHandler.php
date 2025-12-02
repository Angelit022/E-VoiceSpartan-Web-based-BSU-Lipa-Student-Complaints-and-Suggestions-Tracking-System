<?php
class SMSHandler {
    private $apiToken = "01d71561ff1632c0786db2c777e959af787c6d48";
    private $baseUrl = "https://sms.iprogtech.com/api/v1/sms_messages";

    public function sendSMS($phone, $message) {
        $data = [
            'api_token' => $this->apiToken,
            'message' => $message,
            'phone_number' => $phone
        ];

        $ch = curl_init($this->baseUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded'
        ]);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($error) {
            error_log("SMS Error: " . $error);
            return false;
        }

        return json_decode($response, true);
    }
}
?>
