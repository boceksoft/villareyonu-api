<?php

class ExpectedResponse
{
    public $Action;
    public $ReservationId;
    public $homesId;

    public function Insert(): bool
    {
        global $db;
        $query = $db->prepare("insert into KiralamaTakvimi.ExpectedResponses (Action, ReservationId,homesId) values (:Action, :ReservationId,:homesId)");
        return $query->execute([
            "Action" => $this->Action,
            "ReservationId" => $this->ReservationId,
            "homesId" => $this->homesId
        ]);

    }

    public static function List()
    {
        global $db;
        $query = $db->prepare("select count(ReservationId) as Total,Action from KiralamaTakvimi.ExpectedResponses where homesId=:homesId group by Action ");
        $query->execute(["homesId"=>post("homesId")]);
        echo json_encode($query->fetchAll(PDO::FETCH_ASSOC));
    }
}