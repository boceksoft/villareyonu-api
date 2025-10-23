<?php

class Estate
{
    const endPoint = "estate/";
    const domain = "https://www.villavillam.com.tr";
    const cdn = "https://cdn.villavillam.com.tr";

    public static function List()
    {

        $request = new Request([
            "EndPoint" => self::endPoint . "list",
            "Method" => "GET",
            "Data" => [
                "page" => 1,
                "itemCount" => 50,
                "orderByDesc" => "false"
            ]
        ]);
        $request->Send();
        $arr = json_decode($request->getResult(),2);
        echo json_encode($arr);


    }

    public static function Create()
    {

        $json = [];
        //Gelen Json verisini arraya aktar
        $data = json_decode(file_get_contents('php://input'), true);

        if (is_numeric($data["homesId"])) {

            //İlgili villayı çek
            $Estate = Homes::GetById($data["homesId"]);


            if ($Estate) {
                //Yeni Ekle

                if($data["tur"]=="1"){
                    //Yeni Ekle

                    $PostData = [
                        "estateTitle"=> $Estate["baslik"],
                        "estateDescription"=> $Estate["kisa_icerik"],
                        "estateUrl"=> self::domain."/".$Estate["url"],
                        "estateImageUrl"=> self::cdn."/uploads/".$Estate["resim"],
                        "neighbourhoodId"=> 2,
                        "guestPerson"=> (int)$Estate["kisi"],
                        "bedroomCount"=> (int)$Estate["yatak_odasi"],
                        "bedCount"=> (int)$Estate["yatak_sayisi"],
                        "bathCount"=> (int)$Estate["banyo"],
                        "isActive"=> true
                    ];
                    $request = new Request([
                        "EndPoint"=>self::endPoint."create",
                        "Method"=>"Post",
                        "Data"=>json_encode($PostData)
                    ]);
                    $request->Send();
                    $response = json_decode($request->getResult(),2);
                    if ($request->getStatus()=="200"){

                        $json["success"]="Eklendi";

                        $CalendarHomes = new CalendarHomes();
                        $CalendarHomes->EstateId=$response["EstateId"];
                        $CalendarHomes->HomesId=$Estate["id"];
                        $CalendarHomes->CalendarCode=$response["CalendarCode"];
                        $CalendarHomes->Insert();
                        $json["result"]=($response);

                    }else{
                        $json["error"]=$response;
                    }

                }else if($data["tur"]=="2"){
                    //Mevcuta Katıl


                    $PostData["calendarCode"]=$data["calendarCode"];
                    $request = new Request([
                        "EndPoint"=>self::endPoint."join",
                        "Method"=>"Post",
                        "QueryData"=>$PostData
                    ]);
                    $request->Send();
                    $response = json_decode($request->getResult(),2);
                    if ($request->getStatus()=="200"){
                        $json["success"]="Eklendi";
                        $CalendarHomes = new CalendarHomes();
                        $CalendarHomes->EstateId=$response["EstateId"];
                        $CalendarHomes->HomesId=$Estate["id"];
                        $CalendarHomes->CalendarCode=$response["CalendarCode"];
                        $CalendarHomes->Type=1;
                        $CalendarHomes->Insert();
                    }else{
                        $json["error"]=$response;
                    }
                }


            } else {
                $json["error"] = "Geçersiz villa";
            }

        } else {
            $json["error"] = "Geçersiz veri türü";
        }


        echo json_encode($json);


    }

    public static function Delete(){

        $request = new Request([
            "EndPoint" => self::endPoint . "delete",
            "Method" => "Delete",
            "QueryData" => [
                "estateId" => (int)post("estateId"),
            ]
        ]);
        $request->Send();
        $response = json_decode($request->getResult(), 2);
        if ($request->getStatus() == "200") {
            $json["success"] = "Silindi";
        } else {
            $json["error"] = $response;
        }

        echo json_encode($json);
    }

    public static function Leave(){

        $request = new Request([
            "EndPoint" => self::endPoint . "leave",
            "Method" => "Post",
            "Data" => [
                "estateId" => (int)post("estateId"),
            ]
        ]);
        $request->Send();
        $response = json_decode($request->getResult(), 2);
        if ($request->getStatus() == "200") {
            $json["success"] = "Başarılı";
        } else {
            $json["error"] = $response;
        }

        echo json_encode($json);
    }


}