<?php

class ValletApi
{
    private $userName;
    private $password;
    private $shopCode;
    private $hash;

    public function __construct($VirtualPosId)
    {
        global $db;
        $query = $db->prepare("select * from Finance.VirtualPosSettings where VirtualPosId=:VirtualPosId");
        $query->execute(["VirtualPosId"=>$VirtualPosId]);
        $VirtualPosSettings = $query->fetchAll(PDO::FETCH_ASSOC);
        $this->userName = self::getValueByKey($VirtualPosSettings,"Username");
        $this->password = self::getValueByKey($VirtualPosSettings,"Password");
        $this->shopCode = self::getValueByKey($VirtualPosSettings,"ShopCode");
        $this->hash = self::getValueByKey($VirtualPosSettings,"Hash");
    }

    private function hash_generate($string)
    {
        $hash = base64_encode(pack('H*',sha1($this->userName.$this->password.$this->shopCode.$string.$this->hash)));
        return $hash;
    }
    public function create_payment_link($order_data)
    {
        $domain_= BASEAPI;
        $OkUrl = $domain_.'/ValletResponseSuccess';
        $FailUrl = $domain_.'/ValletResponseError';

        //if ($_POST["sipdetay"]=="1"){
        //    $OkUrl = $domain_.'/kart-ile-ode?w=1&p=1&rez='.$_POST["rez_kodu"].'&email='.$_POST["email"];
        //    $FailUrl = $domain_.'/kart-ile-ode?w=2&p=1&rez='.$_POST["rez_kodu"].'&email='.$_POST["email"];
        //}

        $post_data = array(
            'userName' => $this->userName,
            'password' => $this->password,
            'shopCode' => $this->shopCode,
            'productName' => $order_data['productName'],
            'productData' => $order_data['productData'],
            'productType' => $order_data['productType'],
            'productsTotalPrice' => $order_data['productsTotalPrice'],
            'orderPrice' => $order_data['orderPrice'],
            'currency' => $order_data['currency'],
            'orderId' => $order_data['orderId'],
            'locale' => $order_data['locale'],
            'conversationId' => $order_data['conversationId'],
            'buyerName' => $order_data['buyerName'],
            'buyerSurName' => $order_data['buyerSurName'],
            'buyerGsmNo' => $order_data['buyerGsmNo'],
            'buyerIp' => $order_data['buyerIp'],
            'buyerMail' => $order_data['buyerMail'],
            'buyerAdress' => $order_data['buyerAdress'],
            'buyerCountry' => $order_data['buyerCountry'],
            'buyerCity' => $order_data['buyerCity'],
            'buyerDistrict' => $order_data['buyerDistrict'],
            'callbackOkUrl' => $OkUrl,
            'callbackFailUrl' => $FailUrl,
            'module'=>'NATIVE_PHP'
        );
        $post_data['hash'] = $this->hash_generate($post_data['orderId'].$post_data['currency'].$post_data['orderPrice'].$post_data['productsTotalPrice'].$post_data['productType'].$post_data['callbackOkUrl'].$post_data['callbackFailUrl']);

        $response = $this->send_post('https://apiv1.paymax.com.tr/api/create-payment-link',$post_data);
        if ($response['status']=='success' && isset($response['payment_page_url']))
        {
            return $response;
        }
        else
        {
            return $response;
            /*Hatayı Sisteminiz için Yönetin ve Döndürün*/
        }
    }
    private function send_post($post_url,$post_data)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$post_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1) ;
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        curl_setopt($ch, CURLOPT_REFERER, $_SERVER['SERVER_NAME']);

        $response = array();
        if (curl_errno($ch))
        {
            /*Curl sırasında bir sorun oluştu*/
            $response = array(
                'status'=>'error',
                'errorMessage'=>'Curl Geçersiz bir cevap aldı',
            );
        }
        else
        {
            /*Curl Cevabını Alın*/
            $result_origin = curl_exec($ch);
            /*Curl Cevabını jsondan array'a dönüştür*/
            $result = json_decode($result_origin,true);
            if (is_array($result))
            {
                $response = (array) $result;
            }
            else
            {
                $response = array(
                    'status'=>'error',
                    'errorMessage'=>'Dönen cevap Array değildi',
                );
            }
        }
        curl_close($ch);
        return $response;
    }
    public static function  getValueByKey($data, $searchKey) {
        // Sonuç değişkeni
        $result = null;

        // Veriyi tarayıp istenen anahtarı bulma
        foreach ($data as $item) {
            if ($item["Name"] === $searchKey) {
                $result = $item["Value"];
                break;
            }
        }

        // Sonucu döndürme
        return $result;
    }

    /**
     * @return mixed|null
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * @return mixed|null
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @return mixed|null
     */
    public function getShopCode()
    {
        return $this->shopCode;
    }

    /**
     * @return mixed|null
     */
    public function getUserName()
    {
        return $this->userName;
    }
    public static function generateConversationId($reservationNumber) {
        // Zamanı dahil etmek için mevcut zamanı ekleyelim
        $currentTime = time();

        // Rezervasyon numarası ve zamanı birleştirerek benzersiz bir metin oluşturalım
        $combinedText = $reservationNumber . $currentTime;

        // SHA-256 algoritmasını kullanarak bir hash oluşturalım
        $conversationId = hash('sha256', $combinedText);

        // Sonucu döndürelim
        return $conversationId;
    }
}