<?php


class IyzipayBootstrap
{
    public static function options()
    {
        global $db;
        $query = $db->prepare("select * from Finance.VirtualPosSettings where VirtualPosId=:VirtualPosId");
        $query->execute(["VirtualPosId"=>3]);
        $VirtualPosSettings = $query->fetchAll(PDO::FETCH_ASSOC);

        $options = new \Iyzipay\Options();
        $options->setApiKey(ValletApi::getValueByKey($VirtualPosSettings,"IYZIPAY_API_KEY"));
        $options->setSecretKey(ValletApi::getValueByKey($VirtualPosSettings,"IYZIPAY_SECRET_KEY"));
        $options->setBaseUrl('https://api.iyzipay.com');

        return $options;
    }
}