<?php
namespace Procedures\Sample;
use Assert\Assertion;
use Assert\AssertionFailedException;
use Bootstrap\Core;

class WithAuth extends Core {
    private $header_data  = [];

    public function __construct()
    {
        $this->info = "Sample WITH Authorization";
        $this->description = "";
        $this->setBaseEndpoint("https://xxxx.com");
    }

    public function auth_test()
    {
        $auth = "https://xxxx.com/v0.0/auth/login";
        $basic = [
            "Authorization"=>"Basic dVBwY2R3bXhSRFVhaGgycnk1Y1MwZnNhR0cwMjdJWXY6VndTd0FNeEg2ZGxTWGV5WEpleDZVT1diaURHcFJiZEg="
        ];
        $this->setProcedure($auth,"POST",[
            "form_params"=>[
                "identity"=>"ganesha@imceria.com",
                "password"=>"swailai",
                "ref_id"=>"9RpDR",
            ]
        ],$basic);

        $response = $this->getProcedureData();
        if (!isset($response['data']->token)){
            Core::setDebug(json_encode($response));
            return false;
        }
        $this->header_data["Authorization"] = "Bearer ".$response['data']->token;

        return true;
    }

    public function listing_promotion_test()
    {
        $endpoint = "/v0.0.0/gm/promotions/all";
        $this->setProcedure($endpoint,"GET",[],$this->header_data);
        $response = $this->getProcedureData();
        try {
            Assertion::isArray($response);
            Assertion::keyIsset($response,"data");
        } catch (AssertionFailedException $e) {
            self::setDebug($e);
            return false;
        }
        return true;
    }

}