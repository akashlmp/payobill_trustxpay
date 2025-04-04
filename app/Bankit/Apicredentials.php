<?php

namespace App\Bankit {

    use Helpers;
    use App\Models\Api;
    use http\Env\Response;

    class Apicredentials
    {
        private $alg;
        private $hash;
        private $data;

        public function __construct()
        {
            $apis = Api::find(2);
            $this->api_id = $apis->id;
        }
        function getCredentials($mode)
        {
            if ($mode == 'LIVE') {
                return [
                    'base_url' => '',
                    'agentCode' => '',
                    'AgentAuthId' => '',
                    'AgentAuthPassword' => '',
                    'api_id' => $this->api_id,
                ];
            } else {
                return [
                    'base_url' => '',
                    'agentCode' => '',
                    'AgentAuthId' => '',
                    'AgentAuthPassword' => '',
                    'api_id' => $this->api_id,
                ];
            }

        }

        public function encode($header, $payload, $key)
        {
            $this->data = $this->base64url_encode($header) . '.' . $this->base64url_encode($payload);
            return $this->data . '.' . $this->JWS($header, $key);
        }

        private function base64url_encode($data)
        {
            return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
        }

        private function base64url_decode($data)
        {
            return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
        }

        private function JWS($header, $key)
        {
            $json = json_decode($header);
            $this->setAlgorithm($json->alg);
            if ($this->alg == 'plaintext') {
                return '';
            }
            return $this->base64url_encode(hash_hmac($this->hash, $this->data, $key, true));
        }

        private function setAlgorithm($algorithm)
        {
            $hash = 'sha256';
            if (in_array($hash, hash_algos())) $this->hash = $hash;
        }


    }
}
