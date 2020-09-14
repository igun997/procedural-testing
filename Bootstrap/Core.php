<?php
namespace Bootstrap;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use splitbrain\phpcli\CLI;

class Core implements CoreContract {
    public static String $debug_print = "-";
    public String $info;
    public String $description;
    private String $base_endpoint;
    private array $headers;
    private array $procedure_data;
    private array $procedure_header;
    private Client $client;
    private Client $response;
    private CLI $cli;
    private $debug;

    public function setBaseEndpoint(String $base): Core
    {
        $this->base_endpoint = $base;
        $this->client = new Client([
            'base_uri' => $this->base_endpoint,
            'timeout'  => 2.0,
        ]);
        $this->headers = [];
        return $this;
    }

    public function setHeaders(array $headers): Core
    {
        $this->headers = $headers;
        return $this;
    }

    public function setProcedure(String $endpoint,String $type, array $data, array $headers = []): Core
    {
        if (!empty($headers)){
            $this->headers = $headers;
        }

        try {
            $data["headers"]  = $this->headers;
            $response = $this->client->request($type, $endpoint, $data);
            $body = $response->getBody();
            $resp = [];
            if ($body){
                if ($this->_json_validator($body)){
                    $resp = (array) json_decode($body);
                }else{
                    Core::setDebug("Invalid JSON ".((string) $body));
                }
            }
            $this->procedure_data = $resp;
            $this->procedure_header = $response->getHeaders();
        } catch (ClientException $e) {
            Core::setDebug($e);
            return $this;
        }
        return $this;
    }

    public function getProcedureData(): array
    {
        return $this->procedure_data;
    }

    public function getProcedureHeader(): array
    {
        return $this->procedure_header;
    }

    public function getGuzzle():Client
    {
        return $this->response;
    }

    protected function _msg($e,$type="error"){
        if (is_array($e) || is_object($e)){
            $e = json_encode($e);
        }
        if ($type == "error"){
            $this->cli->alert("Fatal Error : ".$e);
        }else{
            $this->cli->info($e);
        }
    }
    private function _json_validator($data=NULL) {
        if (!empty($data)) {
            @json_decode($data);
            return (json_last_error() === JSON_ERROR_NONE);
        }
        return false;
    }

    public static function setDebug($debug)
    {
        if (is_object($debug) || is_array($debug)){
            $debug = json_encode($debug);
        }
        self::$debug_print = $debug;
    }
    public static function getDebug()
    {
        return self::$debug_print;
    }
}