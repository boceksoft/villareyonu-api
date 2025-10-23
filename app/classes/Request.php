<?php

class Request
{
    const baseUrl = "https://backend.kiralamatakvimi.com/api/";

    private $RequestDetails = [];
    private $Result;
    private $Status;
    private $Test=0;


    public function __construct($RequestDetails)
    {
        $this->RequestDetails = $RequestDetails;
    }

    /**
     * @param int $Test
     */
    public function setTest(int $Test): void
    {
        $this->Test = $Test;
    }

    public  function  Send  (){
        global $config;
        $curl = curl_init();
        $apiUrl = self::baseUrl;
        if ($this->Test)
            $apiUrl = "https://backend.beykozrehberi.com/api/";
        curl_setopt_array($curl, array(
            CURLOPT_URL => $apiUrl.$this->RequestDetails["EndPoint"].(isset($this->RequestDetails["QueryData"]) ? "?".http_build_query($this->RequestDetails["QueryData"]):""),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $this->RequestDetails["Method"],
            CURLOPT_POSTFIELDS =>  isset($this->RequestDetails["Data"]) ? $this->RequestDetails["Data"] : [],
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => array(
                'Accept: application/json',
                'Content-Type: application/json',
                'EstablishmentId: '.$config["EstablishmentId"],
                'X-API-Key: '.$config["apiKey"]
            ),
        ));
        $response = curl_exec($curl);
        $info = curl_getinfo($curl);
        curl_close($curl);
        //if (get("EntityId")=="484"){
        //    print_r($info);
        //    exit;
        //}
        $this->setResult($response);
        $this->setStatus($info["http_code"]);
    }

    /**
     * @param mixed $Result
     */
    public function setResult($Result): void
    {
        $this->Result = $Result;
    }

    /**
     * @return mixed
     */
    public function getResult()
    {
        return $this->Result;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->Status;
    }

    /**
     * @param mixed $Status
     */
    public function setStatus($Status): void
    {
        $this->Status = $Status;
    }
}