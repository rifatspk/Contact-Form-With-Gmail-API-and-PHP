<?php

use Google\Service\Gmail;

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/database.php';
$dbAccessToken = new TokenDatabase();
$dbToken =  $dbAccessToken->getTokenFromDB();
// Set up the Gmail API client
$client = new Google_Client();
$client->setApplicationName('');
$client->setAccessType('offline');
$client->setAuthConfig(__DIR__ . '/credentials.json');
if (!empty($dbToken->access_token)) {
    $client->setAccessToken((array)$dbToken);
}
$client->addScope(Gmail::GMAIL_SEND);
if ($dbAccessToken->isTokenEmpty()) {
    // Get authenticated Gmail API client
    if (isset($_GET['code'])) {
        $client->fetchAccessTokenWithAuthCode($_GET['code']);
        $accessToken = $client->getAccessToken();
        $dbAccessToken->UpdateToken(json_encode($accessToken));
    } else {
        $authUrl = $client->createAuthUrl();
        header('Location: ' . filter_var($authUrl, FILTER_SANITIZE_URL));
        exit;
    }
    if ($client->isAccessTokenExpired()) {
        $client->refreshToken($dbAccessToken->refreshTokenInfo());
    }
} else {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $formData = $_POST;


        $senderName = $formData['name'];
        // Set message parameters
        $subject = $formData['subject'] . '-Website Name Contact Form';
        $messageBody = '
        <div background-color: #f2f2f2; font-family: Arial, sans-serif; font-size: 14px; line-height: 1.5; margin: 0;
        padding: 0;>
        <div
             style="background-color: #ffffff; border-radius: 4px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); margin: 20px auto; max-width: 600px; overflow: hidden;">
            <!-- Header -->
            <div style="background-color: #00285e; color: #ffffff; padding: 20px;">
            <h1 style="font-size: 24px; margin: 0;">Contact Form - Website Name</h1>
            </div>
            <!-- Content -->
            <div style="padding: 20px;">
            <table align="center">
            <thead>
            <tr>
            <th align="center">Query</th>
            <th align="center">Answer</th>
            </tr>
            </thead>
            <tbody>';
        foreach ($formData as $formKey => $formValue) {
            if (is_array($formValue)) {
                $value = $formValue[0];
            } else {
                $value = $formValue;
            }
            $messageBody .= '<tr>
            <td align="left"> <strong style="text-transform: capitalize;">' . $formKey . ': </strong> </td>
            <td align="left">' . $value . '</td>
        </tr>';
        }
        $messageBody .= '</tbody>
        </table>
        </div>
        </div>
        </div>
        ';
        $boundary = uniqid();
        $message = new Swift_Message();
        $message->setSubject($subject);
        $message->setFrom(['youremail@gmail.com' => $senderName]);
        $message->setTo(['youremail@gmail.com']);
        $message->setBody($messageBody, 'text/html');
        function base64url_encode($data)
        {
            return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
        }
        // Convert the message to a raw format for sending with the Gmail API
        $rawMessage = base64url_encode($message->toString());
        // Create email message
        $message = new Google_Service_Gmail_Message();
        $message->setRaw($rawMessage);
        // Send email message
        $service = new Google_Service_Gmail($client);
        try {
            $message = $service->users_messages->send('me', $message);
            $response = array(
                'success' => true,
                'message' => 'Thank You for contact with us. We will get touch with you very soon'
            );
            header('Content-Type: application/json');
            echo json_encode($response);
        } catch (Exception $e) {
            $response = array(
                'success' => false,
                'message' => 'An error occurred while sending your message. Please try again later.' . $e,
            );
            header('Content-Type: application/json');
            echo json_encode($response);
        }
    }
}
