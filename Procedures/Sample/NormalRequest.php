<?php
namespace Procedures\Sample;

use Assert\Assert;
use Assert\Assertion;
use Bootstrap\Core;
use splitbrain\phpcli\CLI;

class NormalRequest extends Core
{
    public function __construct()
    {
        $this->info = "Sampe Request";
        $this->description = "";
        $this->setBaseEndpoint("https://httpbin.org");
    }

    public function get_test(CLI $instance){
        $this->setProcedure("/get","GET",["query"=>[
            "balh"=>"duaar"
        ]]);
        $resData = $this->getProcedureData();
//        Core::setDebug("GET");
        //$instance->info(json_encode($resData));
        if (!empty($resData)){
            return true;
        }
        return false;
    }
    public function post_test(CLI $instance){
        $this->setProcedure("/post","POST",[
            "form_params"=>[
                "balh"=>"duaar"
            ]
        ]);
        $resData = $this->getProcedureData();
        //$instance->info(json_encode($resData));
        if (!empty($resData)){
            return true;
        }
        return false;
    }

    public function put_test(CLI $instance){
        $this->setProcedure("/put","PUT",[
            "json"=>[
                "balh"=>"duaar"
            ]
        ]);
        $resData = $this->getProcedureData();
        //$instance->info(json_encode($resData));
        if (!empty($resData)){
            return true;
        }
        return false;
    }

    public function delete_test(CLI $instance){
        $this->setProcedure("/delete","DELETE",[
            "query"=>[
                "balh"=>"duaar"
            ]
        ]);
        $resData = $this->getProcedureData();
        //$instance->info(json_encode($resData));
        if (!empty($resData)){
            return true;
        }
        return false;
    }

}