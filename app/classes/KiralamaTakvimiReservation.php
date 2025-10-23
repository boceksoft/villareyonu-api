<?php

class KiralamaTakvimiReservation
{
    const endPoint = "reservation/";

    public static function Check(int $estateId,string $checkInDate,string $checkOutDate)
    {
        $request = new Request([
            "EndPoint" => self::endPoint . "check",
            "Method" => "POST",
            "Data" => json_encode([
                "estateId" => $estateId,
                "checkInDate" => $checkInDate,
                "checkOutDate" => $checkOutDate,
                "isEstateOwnerRulesEnabled" => true
            ])
        ]);
        //if ($estateId==9336)
        //    $request->setTest(1);
        $request->Send();
        $arr = json_decode($request->getResult(),2);
        return $arr;


    }

    public static function AvailableReservations(int $estateId,string $checkInDate,string $checkOutDate)
    {
        $request = new Request([
            "EndPoint" => self::endPoint . "availablereservations",
            "Method" => "POST",
            "QueryData" => ([
                "estateId" => $estateId,
                "startDate" => $checkInDate,
                "endDate" => $checkOutDate
            ])
        ]);
        $request->Send();
        $arr = json_decode($request->getResult(),2);
        return $arr;


    }

    public static function Create($data)
    {
        $request = new Request([
            "EndPoint" => self::endPoint . "create",
            "Method" => "POST",
            "Data" => json_encode($data)
        ]);
        $request->Send();
        $arr = json_decode($request->getResult(),2);
        return $arr;


    }

    public static function Approve($ReservationId)
    {
        $request = new Request([
            "EndPoint" => self::endPoint . "approve",
            "Method" => "POST",
            "QueryData" => [
                "reservationId"=>$ReservationId,
            ]
        ]);
        $request->Send();
        return json_decode($request->getResult(),2);


    }

}