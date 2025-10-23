<?php


$tcmbMirror = 'https://doviz.boceksoft.com/kurlar.xml';
$curl = curl_init($tcmbMirror);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($curl, CURLOPT_URL, $tcmbMirror);

$dataFromtcmb = curl_exec($curl);

if ($dataFromtcmb != "") {
    function TCMB_Converter($from = 'TRY', $to = 'USD', $val = 1)
    {
        global $dataFromtcmb;

        // Sistemimizde Simplexml ve Curl fonksiyonları var mı kontrol ediyoruz.
        if (!function_exists('simplexml_load_string') || !function_exists('curl_init')) {
            return 'Simplexml extension missing.';
        }

        // Başlangıç için nereden/nereye değerlerini 1 yapıyoruz çünkü TRY'nin bir karşılığı yok.
        $CurrencyData = [
            'from' => 1,
            'to' => 1
        ];

        // XML verisini SimpleXML'e aktararak bir class haline getiriyoruz.
        $Currencies = simplexml_load_string($dataFromtcmb);

        // Bütün verileri foreach ile gezerek arıyoruz ve nereden/nereye değerlerimize eşitliyoruz.
        foreach ($Currencies->Currency as $Currency) {
            if ($from == $Currency['CurrencyCode']) $CurrencyData['from'] = $Currency->BanknoteSelling;
            if ($to == $Currency['CurrencyCode']) $CurrencyData['to'] = $Currency->BanknoteSelling;
        }

        // Hesaplama işlemini yaparak return ediyoruz.
        return round(($CurrencyData['to'] / $CurrencyData['from']) * $val, 10);
    }




        $query = $db->prepare("insert into Finance.Rate (RateDate,MainCurrencyId) values (:RateDate,3)");
        $i = $query->execute([
            "RateDate" => date("Y-m-d")
        ]);

        if ($i) {
            $RateId = $db->lastInsertId();

            $query = $db->query("Select * from Finance.Currency WHERE IsDeleted=0");
            $Currency = $query->fetchAll(PDO::FETCH_ASSOC);

            foreach ($Currency as $From) {
                foreach ($Currency as $To) {
                    $query = $db->prepare("insert into Finance.RateDetail (RateId, FromCurrencyId, ToCurrencyId, Buy) values (:RateId, :FromCurrencyId, :ToCurrencyId, :Buy)");
                    $query->execute([
                        "RateId" => $RateId,
                        "FromCurrencyId" => $To["CurrencyId"],
                        "ToCurrencyId" => $From["CurrencyId"],
                        "Buy" => TCMB_Converter($From["CurrencyCode"], $To["CurrencyCode"], 1)
                    ]);
                }
            }


        }else{
            echo "xxx";
        }



}else{
    echo "Veri alınamadı.";
}




