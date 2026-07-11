<?php
    declare(strict_types=1);

    define("QL_EOL", "\r\n");
    
    class TeslaServerApiBase {
        protected readonly bool $debug;
        private string $privateCertificate = "";
        private string $publicCertificate = "";
        private string $publicCertificateHashAlgorithm = "";
        private string $requestSignAlgorithm = "";
        private int $clockSkewInSeconds;

        //Readable header name
		protected array $headerNames = array (
    		"apiKey"=>"x-ql-api-key", 
    		"issueInstant"=>"x-ql-issue-instant",
    		"signature"=>"x-ql-signature"
    	);

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
            $this->debug = $showDebug;
            $abortLoad = false;
            if(!function_exists('curl_version')){
            	echo "Curl not found<br />";
            	$abortLoad = true;
            }
            else
            {
				if($showDebug) {
            		echo "Curl version number: " . curl_version()['version_number'] . "<br />";
        		}
            }
    
    		if(!defined('OPENSSL_VERSION_NUMBER') || !OPENSSL_VERSION_NUMBER){
            	echo "OpenSSL not found<br />";
            	$abortLoad = true;
            }
            else
            {
				if($showDebug) {
            		echo "OpenSSL version number: " . OPENSSL_VERSION_NUMBER . "<br />";
        		}
            }

            if($abortLoad)
            {
            	exit();
            }

			$this->clockSkewInSeconds = $clockSkewInSeconds;
			$this->requestSignAlgorithm = $requestSignAlgorithm;
        	
        	if($clientPrivateKey)
        	{
				if(!$externalLoadedPrivateCert)
				{
		        	$result = $this->LoadCertificate($clientPrivateKey, $pfxPassword, true);
			       	$this->privateCertificate = $this->GetPrivateKeyFromCertData($result);
	        	}
	        	else
	        	{
	        		$this->privateCertificate = $clientPrivateKey;
	        	}
			}

			if($servicePublicKey)
			{
				if(!$externalLoadedPublicCert)
				{
		        	$result = $this->LoadCertificate($servicePublicKey);
		        	$this->publicCertificate = $this->GetCertificateFromCertData($result);
                    $this->publicCertificateHashAlgorithm = openssl_x509_parse($this->publicCertificate)["signatureTypeLN"];
	        	}
	        	else
	        	{
	        		$this->publicCertificate = $servicePublicKey;
	        	}
        	}
        }
        
        private function ComputeSignature($data = false) {
        	if(!$data || !$this->privateCertificate || !$this->requestSignAlgorithm) {
        		return "";
        	}
        	
        	$pkeyid = openssl_pkey_get_private($this->privateCertificate);
			if(openssl_sign($data, $signature, $pkeyid, $this->requestSignAlgorithm)){
				return $signature;
			}

			echo "Error creating signature, abort<br />";
			exit();
        }

        private function VerifySignature($body, $url, $headers) : bool
        {
			$instant = $headers[$this->headerNames['issueInstant']];
			$signature = $headers[$this->headerNames['signature']];

            if(!$this->publicCertificate) {
                return false;
            }

			$sigData = urldecode($url) . QL_EOL . $instant;
			if($body){
				$sigData .= QL_EOL . $body;
			}

			if(date_parse($instant)['year'] > 0)
			{
                $issueInstantMinimum = date('c', time() - $this->clockSkewInSeconds);
                $issueInstantMaximum = date('c', time() + $this->clockSkewInSeconds);

                if($instant < $issueInstantMinimum || $instant > $issueInstantMaximum)
                {
                	echo('<br />Signature issue date is invalid<br />');
                	exit();
                }
			}

			if($this->debug)
			{
				echo '<strong>Signature data:<br /><pre>';
				print_r($signature);
				echo '<br>';
				print_r($sigData);
				echo '<hr>';
                print($this->publicCertificateHashAlgorithm);
                echo '<hr>';
				print_r(bin2hex($sigData));
			}

			$sigResult = openssl_verify($sigData, base64_decode($signature), $this->publicCertificate, $this->publicCertificateHashAlgorithm);
        	return ($sigResult == 1);
        }

        private function SignRequest(array &$headers, string $url, $data = false)
		{
			$timeStamp = date('c', time());
			$timeStamp = str_replace('+00:00', 'Z', $timeStamp);

			if(!$this->privateCertificate)
			{
				echo "Tried to SignRequest without any private key";
				exit();
			}

			$decodedUrl = urldecode($url);
			array_push($headers, $this->headerNames['issueInstant'] . ": " . $timeStamp);

			$sigData = $decodedUrl . QL_EOL . $timeStamp;
			if($data && is_array($data) && count($data) > 0)
			{
				$sigData .= QL_EOL . json_encode($data);
			}

			$computedSignature = $this->ComputeSignature($sigData);

			$sigBase64 = base64_encode($computedSignature);

			array_push($headers, $this->headerNames['signature'] . ": " . $sigBase64);
		}

        private function GetPrivateKeyFromCertData(array $cert_info) : string
        {
    		if(array_key_exists("pkey", $cert_info)){
    			return $cert_info["pkey"];
    		}

    		return "";
        }

        private function GetCertificateFromCertData(array $cert_info) : string
        {
    		if(array_key_exists("cert", $cert_info)){
    			return $cert_info["cert"];
    		}

    		return "";
        }

       	private function LoadCertificate(string $certificatePath, string $pfxPassword = "", bool $isPrivateKey = false) : array
       	{
       		if($this->debug){
       			echo "Reading certificate: " . $certificatePath . "<br />";
       		}

       		if (!$cert_store = file_get_contents($certificatePath)) {
			    echo "Error: Unable to read the cert file<br />";
			    exit;
			}

			if($isPrivateKey && $this->debug)
			{
				echo "Loading private key certificate <br>";
			}

			if ($isPrivateKey && openssl_pkcs12_read($cert_store, $cert_info, $pfxPassword)) {
				if($this->debug){
					echo "Read private certificate: " . $certificatePath . "<br />";
				}   
				return $cert_info;
			}

			if($isPrivateKey && $this->debug)
			{
				echo "Private key certificate load failed<br>";
				exit();
			}

			//Assume public-key as no password given.

			$certificateCApemContent = '-----BEGIN CERTIFICATE-----'.PHP_EOL
            .chunk_split(base64_encode($cert_store), 64, PHP_EOL)
            .'-----END CERTIFICATE-----'.PHP_EOL;

			if($cert_info = openssl_x509_read($certificateCApemContent)) {
				if($this->debug){
					echo "Read public certificate: " . $certificatePath . "<br />";
				}   
				openssl_x509_export($cert_info, $cert_text);
				return array("cert"=>$cert_text);
			}
			
		    echo "Error: Unable to read the cert store.<br />";
		    exit;

		    return null;
		}

		private function parseHeaderAndGetSignatureData(string $serverResponse, int $header_size)
		{
				//Headers in PHP are just one string, so we need to split and flatten array, so we easier can get our values.
				$headerData = array_merge(...array_map(function ($input) {	return explode(": ", $input); }, explode(PHP_EOL, substr($serverResponse, 0, $header_size))));
				$idxInstant = array_search($this->headerNames['issueInstant'], $headerData);
				$idxSignature = array_search($this->headerNames['signature'], $headerData);

				//Make sure both headers are found, else we get wrong results.
                if($idxInstant && $idxSignature)
				{
					return array($this->headerNames['issueInstant'] => $headerData[$idxInstant+1], $this->headerNames['signature'] => $headerData[$idxSignature+1]);
				}
				else
				{
					return array($this->headerNames['issueInstant'] => "", $this->headerNames['signature'] => "");
				}
		}

        // Method: POST, PUT, GET etc
		// Data: array("param" => "value") ==> index.php?param=value

		protected function CallAPI(string $method, string $url, array $headers, $data = false)
		{
		    $curl = curl_init();

		    array_push($headers, 'Content-Type:application/json');

		    switch ($method)
		    {
		        case "POST":
		            curl_setopt($curl, CURLOPT_POST, 1);
		            if ($data)
		                curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
		            break;
		        case "PUT":
		            curl_setopt($curl, CURLOPT_PUT, 1);
		            if ($data)
		                curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
		            break;
				case "PATCH":
		            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
		            if ($data)
		               	curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
		            break;
		        default:
		        	curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
		            if ($data){
		                $url = sprintf("%s?%s", $url, http_build_query($data));
		                $data = false;
		            }
		    }

			if($this->privateCertificate) {
				$this->SignRequest($headers, $url, $data);
		    }

		    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		    curl_setopt($curl, CURLOPT_URL, $url);
		    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		    curl_setopt($curl, CURLOPT_VERBOSE, true);

			$result = array("result"=>"");

            //We always want Headers
			curl_setopt($curl, CURLOPT_HEADER, 1);
	    	
            //First we call server api
	    	$serverResponse = curl_exec($curl);
			$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

			if($this->debug){
				echo 'Response code: ' . $httpcode . '<br/>';
			}

	    	if(!$serverResponse) {
	    		return "";
	    	}

            //Extract headers and body from Response
			$header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
			$response_headers = $this->parseHeaderAndGetSignatureData($serverResponse, $header_size);
			$body = substr($serverResponse, $header_size);

			$signature = '';
			if (array_key_exists($this->headerNames['signature'], $response_headers)) {
			    $signature = $response_headers[$this->headerNames['signature']];
			}

            //If we have signature or a public cert, assume signature always.
		    if(strlen($signature) > 0 || $this->publicCertificate) {

				if($this->debug){
					echo "<div>Verifying Signature: ". $signature ."</div>";
				}
				
				if(!$this->VerifySignature($body, $url, $response_headers)){
					if($this->debug){
						echo('<br />Response signature could not be verified!:<br />');
					}
                    //Abort as Signature could not be verified!
                	exit();
				}
		    }
		    else
		    {
				if($this->debug){
					echo "<div>No verifying signature</div>";
				}
		    }

		    $result = json_decode($body, true);

			if (curl_errno($curl)) {
			    print "Error: " . curl_error($curl);
			    exit();
			}

		    if($this->debug)
		    {
		    	echo "<div style='border: 1px solid #000; margin: 5px; padding: 8px; white-space: no-wrap; overflow: hidden; width: 768px;'><strong>Curl executed</strong><pre>" . $url . "</pre></div>";
		    }

		    curl_close($curl);

			
			if($httpcode >= 200 && $httpcode < 300)
			{
				if($this->debug)
				{
					echo 'result: ' . var_dump($result) . "<br>";
				}
				
				if($result && array_key_exists('result', $result))
				{
					return $result['result'];
				}else{
					return "";
				}
			}

			echo "Unable to process request -> received: " . $httpcode . " -> " . var_dump($result);
			exit();
		}
    }
?>