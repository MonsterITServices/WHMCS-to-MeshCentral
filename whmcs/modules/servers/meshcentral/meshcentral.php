<?php

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

// Require the API library
require_once __DIR__ . '/lib/MCH_API.php';

/**
 * Define module metadata.
 */
function meshcentral_MetaData() {
    return [
        'DisplayName' => 'MeshCentral Connector',
        'APIVersion' => '1.1',
        'RequiresServer' => true,
    ];
}

/**
 * Define product configuration options shown in WHMCS product setup.
 */
function meshcentral_ConfigOptions() {
    return [
        'ServerURL' => [
            'FriendlyName' => 'MeshCentral Server URL',
            'Type' => 'text',
            'Size' => '50',
            'Description' => 'e.g., https://mon.monster-it.co.uk',
        ],
        'APIKey' => [
            'FriendlyName' => 'API Login Key',
            'Type' => 'password',
            'Size' => '100',
            'Description' => 'Generated from your MeshCentral account -> My Account -> API Keys',
        ],
    ];
}

/**
 * Called when a new service is provisioned.
 */
function meshcentral_CreateAccount(array $params) {
    $serverUrl = $params['configoption1'];
    $apiKey = $params['configoption2'];
    $clientId = $params['userid'];
    $clientEmail = $params['clientsdetails']['email'];
    $serviceId = $params['serviceid'];

    try {
        $api = new MCH_API($serverUrl, $apiKey);

        // 1. Create a Device Group for the client.
        $groupName = "WHMCS Client {$clientId} (Service {$serviceId})";
        $deviceGroup = $api->createDeviceGroup($groupName);
        $groupId = $deviceGroup['_id'];

        // 2. Create a User in MeshCentral for the WHMCS client.
        $username = "whmcs_{$clientId}";
        $userPassword = 'P@ssw0rd' . bin2hex(random_bytes(12)); // Random, secure password
        $permissions = [ $groupId => [ "fullRights" => true ] ];
        $api->createUser($username, $userPassword, $clientEmail, $permissions);

        // 3. Save key data to WHMCS for future management.
        \WHMCS\Database\Capsule::table('tblhosting')
            ->where('id', $serviceId)
            ->update([
                'domain' => $groupId,
                'username' => $username,
            ]);

    } catch (Exception $e) {
        logModuleCall('meshcentral', __FUNCTION__, $params, $e->getMessage(), $e->getTraceAsString());
        return "Error connecting to MeshCentral: " . $e->getMessage();
    }

    return 'success';
}

/**
 * Called when a service is terminated.
 */
function meshcentral_TerminateAccount(array $params) {
    $serverUrl = $params['configoption1'];
    $apiKey = $params['configoption2'];
    $groupId = $params['domain'];
    $username = $params['username'];

    try {
        $api = new MCH_API($serverUrl, $apiKey);

        // 1. Delete the MeshCentral user
        $user = $api->getUser($username);
        if ($user) {
            $api->deleteUser($user['_id']);
        }

        // 2. Delete the device group
        if ($groupId) {
             $api->deleteDeviceGroup($groupId);
        }

    } catch (Exception $e) {
        logModuleCall('meshcentral', __FUNCTION__, $params, $e->getMessage(), $e->getTraceAsString());
        return "Error during termination: " . $e->getMessage();
    }

    return 'success';
}

/**
 * Generates the output for the client area product details page.
 */
function meshcentral_ClientArea(array $params) {
    $serverUrl = $params['configoption1'];
    $apiKey = $params['configoption2'];
    $groupId = $params['domain'];
    $username = $params['username'];

    try {
        $api = new MCH_API($serverUrl, $apiKey);
        
        // Generate a single sign-on (SSO) link for the client.
        $ssoLink = $api->createLoginToken($username);

        // Fetch all devices in the client's group.
        $devices = $api->getDevicesInGroup($groupId);

        // Get the agent installer link for their group.
        $agentLink = $api->getAgentInstallLink($groupId);

        return [
            'templatefile' => 'templates/clientareadetails',
            'vars' => [
                'ssoLink' => $ssoLink,
                'devices' => $devices,
                'agentLink' => $agentLink,
                'meshServerUrl' => rtrim($serverUrl, '/'),
            ],
        ];

    } catch (Exception $e) {
        logModuleCall('meshcentral', __FUNCTION__, $params, $e->getMessage(), $e->getTraceAsString());
        return [
            'templatefile' => 'templates/clientareadetails',
            'vars' => [
                'error' => 'Could not retrieve device information. Please contact support.',
            ],
        ];
    }
}