<?php

   /*
     * Quardlock Server Api library for PHP by Quardlock ApS
     * version: 1.0
     * release date: 08-01-2024
     */

	declare(strict_types=1);

    namespace Quardlock\library;

    require_once 'TeslaServerApiBase.php';

    class ServerApi extends \TeslaServerApiBase {
        private string $serverApiBaseUrl = 'https://auth.quardlock.com/api/server';

		/**
		* returns the client session token id string
		*
		* @param string $clientPrivateKey
		* @param string $servicePublicKey
		* @param string $requestSignAlgorithm
		* @param string $pfxPassword
		* @param string $clockSkewInSeconds
		* @param string $externalLoadedCerts
		* @param string $showDebug
		* 
		* @return void.
		*/
        public function __construct(
        		string $clientPrivateKey, 
        		string $servicePublicKey, 
        		string $requestSignAlgorithm, 
        		string $pfxPassword, 
        		int $clockSkewInSeconds = 60,
        		bool $externalLoadedPrivateCert = false,
        		bool $externalLoadedPublicCert = false,
        		bool $showDebug = false
        ) {
            parent::__construct(
                $clientPrivateKey,
                $servicePublicKey,
                $requestSignAlgorithm,
                $pfxPassword,
                $clockSkewInSeconds,
                $externalLoadedPrivateCert,
                $externalLoadedPublicCert,
                $showDebug
            );
        }

		## Public Methods START ##

		/**
		* returns the client session token id string
		*
		* @param string $apiKey apikey to access account and get a client session token for.
		* @param array $endpointUrls urls this session token should allow
		* @param array $clientIP (OPTIONAL) lock client session token to requests from this IP only.
		* 
		* @return string.
		*/
		public function InitializeApiClientSession(string $apiKey, array $endpointUrls, ?string $clientIP) : string
        {
			if($this->debug) {
            	echo "<hr>Quardlock ServerApi InitializeApiClientSession<br />";
        	}

            $query = array("EndpointUrls" => $endpointUrls);
        	if($clientIP)
        	{
                $query['clientIP'] = $clientIP;
        	}

			return $this->CallAPI("GET", $this->serverApiBaseUrl . "/InitializeApiClientSession", array($this->headerNames['apiKey'] . ": " . $apiKey), $query);
        }

        public function RevokeApiClientSessionToken(string $apiKey, string $tokenToRevoke) {
			if($this->debug) {
            	echo "<hr>Quardlock ServerApi RevokeApiClientSessionToken<br />";
        	}

			return $this->CallAPI("DELETE", 
				$this->serverApiBaseUrl . "/RevokeApiClientSessionToken", 
				array($this->headerNames['apiKey'] . ": " . $apiKey), 
				array("sessionTokenId"=>$tokenToRevoke)
			);
        }

        public function IsTokenLocked(string $apiKey, string $serialNumber) {
			if($this->debug) {
            	echo "<hr>Quardlock ServerApi IsTokenLocked<br />";
        	}

			return $this->CallAPI("GET", 
				$this->serverApiBaseUrl . "/IsTokenLocked", 
				array($this->headerNames['apiKey'] . ": " . $apiKey), 
				array("serialNumber"=>$serialNumber)
			);
        }

        public function DeleteToken(string $apiKey, string $serialNumber) {
			if($this->debug) {
            	echo "<hr>Quardlock ServerApi calling DeleteToken".$this->serverApiBaseUrl."<br />";
        	}

			return $this->CallAPI("DELETE", 
				$this->serverApiBaseUrl . "/DeleteToken", 
				array($this->headerNames['apiKey'] . ": " . $apiKey), 
				array("serialNumber"=>$serialNumber)
			);
        }

        public function LockToken(string $apiKey, string $serialNumber) {
			if($this->debug) {
            	echo "<hr>Quardlock ServerApi LockToken<br />";
        	}

			return $this->CallAPI("PATCH", 
				$this->serverApiBaseUrl . "/LockToken", 
				array($this->headerNames['apiKey'] . ": " . $apiKey), 
				array("serialNumber"=>$serialNumber)
			);
        }

        public function UnlockToken(string $apiKey, string $serialNumber) {
			if($this->debug) {
            	echo "<hr>Quardlock ServerApi UnlockToken<br />";
        	}

			return $this->CallAPI("PATCH", 
				$this->serverApiBaseUrl . "/UnlockToken", 
				array($this->headerNames['apiKey'] . ": " . $apiKey), 
				array("serialNumber"=>$serialNumber)
			);
        }

        public function IsTokenKeyLocked(string $apiKey, string $serialNumber, string $tokenKeyMode = "") {
			if($this->debug) {
            	echo "<hr>Quardlock ServerApi IsTokenKeyLocked<br />";
        	}

        	$query = array("serialNumber"=>$serialNumber);
        	if($tokenKeyMode !== "")
        	{
        		$query['tokenKeyMode'] = $tokenKeyMode;
        	}

			return $this->CallAPI("GET", 
				$this->serverApiBaseUrl . "/IsTokenKeyLocked", 
				array($this->headerNames['apiKey'] . ": " . $apiKey), 
				$query
			);
        }

        public function LockTokenKey(string $apiKey, string $serialNumber, string $tokenKeyMode) {
			if($this->debug) {
            	echo "<hr>Quardlock ServerApi LockTokenKey<br />";
        	}

        	$query = array("serialNumber"=>$serialNumber);
        	if($tokenKeyMode !== "")
        	{
        		$query['tokenKeyMode'] = $tokenKeyMode;
        	}

			return $this->CallAPI("PATCH", 
				$this->serverApiBaseUrl . "/LockTokenKey", 
				array($this->headerNames['apiKey'] . ": " . $apiKey), 
				$query
			);
        }

        public function UnlockTokenKey(string $apiKey, string $serialNumber, string $tokenKeyMode) {
			if($this->debug) {
            	echo "<hr>Quardlock ServerApi UnlockTokenKey<br />";
        	}

        	$query = array("serialNumber"=>$serialNumber);
        	if($tokenKeyMode !== "")
        	{
        		$query['tokenKeyMode'] = $tokenKeyMode;
        	}

			return $this->CallAPI("PATCH", 
				$this->serverApiBaseUrl . "/UnlockTokenKey", 
				array($this->headerNames['apiKey'] . ": " . $apiKey), 
				$query
			);
        }

        public function Authenticate(string $apiKey, string $serialNumber, string $otp, string $site = "", string $clientIP = "") {
			if($this->debug) {
            	echo "<hr>Quardlock ServerApi Authenticate<br />";
        	}

        	$query = array("serialNumber"=>$serialNumber,
        					"otp"=>$otp,
        					"site"=>$site,
                            "clientIP"=>$clientIP);

			return $this->CallAPI("POST", 
				$this->serverApiBaseUrl . "/Authenticate", 
				array($this->headerNames['apiKey'] . ": " . $apiKey), 
				$query
			);
        }

        public function AuthenticateWithPayload(string $apiKey, string $serialNumber, string $otp, string $payload, string $site = "", string $clientIP = "") {
			if($this->debug) {
            	echo "<hr>Quardlock ServerApi Authenticate<br />";
        	}

        	$query = array("serialNumber"=>$serialNumber,
        					"otp"=>$otp,
        					"payload"=>$payload,
        					"site"=>$site,
                            "clientIP"=>$clientIP);

			return $this->CallAPI("POST", 
				$this->serverApiBaseUrl . "/Authenticate", 
				array($this->headerNames['apiKey'] . ": " . $apiKey), 
				$query
			);
        }

        public function AuthenticateWithMode(string $apiKey, string $serialNumber, string $otp, string $tokenKeyMode, string $site = "", string $clientIP = "") {
			if($this->debug) {
            	echo "<hr>Quardlock ServerApi AuthenticateWithMode<br />";
        	}

        	$query = array("serialNumber"=>$serialNumber,
        					"mode"=>$tokenKeyMode,
        					"otp"=>$otp,
        					"site"=>$site,
                            "clientIP"=>$clientIP);

			return $this->CallAPI("POST", 
				$this->serverApiBaseUrl . "/AuthenticateWithMode", 
				array($this->headerNames['apiKey'] . ": " . $apiKey), 
				$query
			);
        }

        public function AuthenticateWithModeAndPayload(string $apiKey, string $serialNumber, string $otp, string $tokenKeyMode, string $payload, string $site = "",
            string $clientIP = "") {
			if($this->debug) {
            	echo "<hr>Quardlock ServerApi AuthenticateWithPayload<br />";
        	}

        	$query = array("serialNumber"=>$serialNumber,
        					"mode"=>$tokenKeyMode,
        					"otp"=>$otp,
        					"payload"=>$payload,
        					"site"=>$site,
                            "clientIP"=>$clientIP);

			return $this->CallAPI("POST", 
				$this->serverApiBaseUrl . "/AuthenticateWithPayload", 
				array($this->headerNames['apiKey'] . ": " . $apiKey), 
				$query
			);
        }

		public function AuthenticateWebAuthnToken(string $apiKey, string $webAuthnSessionId, string $authenticatorData64Encoded, string $clientDataBase64Encoded, string $credentialId, string $signature, string $credentialType, string $userHandle, string $relayPointId, string $site = "", string $clientIP = "") 
        {
			if($this->debug) {
            	echo "<hr>Quardlock ServerApi AuthenticateWebAuthnToken<br />";
        	}

        	$query = array("webAuthnSessionId"=>$webAuthnSessionId,
        					"authenticatorData64Encoded"=>$authenticatorData64Encoded,
        					"clientDataBase64Encoded"=>$clientDataBase64Encoded,
        					"credentialId"=>$credentialId,
        					"signature"=>$signature,
        					"credentialType"=>$credentialType,
        					"userHandle"=>$userHandle,
        					"relayPointId"=>$relayPointId,
        					"site"=>$site,
                            "clientIP"=>$clientIP);

			return $this->CallAPI("POST", 
				$this->serverApiBaseUrl . "/AuthenticateWebAuthnToken", 
				array($this->headerNames['apiKey'] . ": " . $apiKey), 
				$query
			);
		}

        ## Public Methods END ##
    }   
?>