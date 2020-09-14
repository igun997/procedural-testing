<?php
namespace  Bootstrap;

use splitbrain\phpcli\CLI;

interface CoreContract {
    public function setBaseEndpoint(String $base):Core;
    public function setHeaders(Array $headers):Core;
    public function setProcedure(String $endpoint,String $type,array $data,array $headers = []):Core;
    public function getProcedureData():array ;
    public function getProcedureHeader():array ;
}