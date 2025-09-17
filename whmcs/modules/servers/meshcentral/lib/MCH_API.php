<?php

class MCH_API {
    private $serverUrl;
    private $cookieJar;

    public function __construct($serverUrl, $apiKey) {
        $this->serverUrl = rtrim($serverUrl, '/');
        $this->cookieJar = tempnam(sys_get_temp_dir(), 'MCH_COOKIE');
        $this->login($apiKey);
    }
    
    private function login($apiKey) {
        $this->sendCommand(['action' => 'login', 'loginkey' => $apiKey]);
    }
    
    private function sendCommand($command) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->serverUrl . '/api.ashx');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($command));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookieJar);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookieJar);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        
        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            throw new Exception('cURL Error: ' . curl_error($ch));
        }
        curl_close($ch);
        
        $decoded = json_decode($response, true);
        if (!$decoded) {
            throw new Exception('Invalid JSON response from MeshCentral API.');
        }
        if (isset($decoded['result']) && $decoded['result'] !== 'ok') {
            throw new Exception('MeshCentral API Error: ' . ($decoded['msg'] ?? 'Unknown error'));
        }
        return $decoded;
    }

    public function createDeviceGroup($name) {
        return $this->sendCommand(['action' => 'addDeviceGroup', 'name' => $name]);
    }

    public function deleteDeviceGroup($groupId) {
        return $this->sendCommand(['action' => 'deleteDeviceGroup', 'devicegroupid' => $groupId]);
    }

    public function createUser($username, $password, $email, $permissions) {
        return $this->sendCommand([
            'action' => 'addUser',
            'name' => $username,
            'pass' => $password,
            'email' => $email,
            'permissions' => $permissions,
        ]);
    }

    public function deleteUser($userId) {
        return $this->sendCommand(['action' => 'deleteUser', 'userid' => $userId]);
    }

    public function getUser($username) {
        $response = $this->sendCommand(['action' => 'getUsers']);
        foreach ($response['users'] as $user) {
            if ($user['name'] === $username) {
                return $user;
            }
        }
        return null;
    }
    
    public function getAgentInstallLink($groupId) {
        $response = $this->sendCommand(['action' => 'getmeshes']);
        $meshId = '';
        foreach ($response['meshes'] as $mesh) {
            if ($mesh['name'] === 'Default') {
                $meshId = $mesh['_id'];
                break;
            }
        }
        return $this->serverUrl . "/meshagents?id=2&meshid=" . urlencode($meshId) . "&installflags=5&groupid=" . urlencode($groupId);
    }
    
    public function getDevicesInGroup($groupId) {
        $nodes = $this->sendCommand(['action' => 'getNodes', 'groupid' => $groupId]);
        return $nodes['nodes'] ?? [];
    }
    
    public function createLoginToken($username) {
        $tokenData = $this->sendCommand(['action' => 'createLoginToken', 'username' => $username]);
        if (empty($tokenData['token'])) {
            throw new Exception('Failed to create login token.');
        }
        return $this->serverUrl . "?logintoken=" . $tokenData['token'];
    }

    public function __destruct() {
        if (file_exists($this->cookieJar)) {
            unlink($this->cookieJar);
        }
    }
}