<?php

class CustomInstallment
{
    public static function GetInstallments($Reservation,$Bin=null)
    {
        global $db;

        if($Reservation["taksit"]!=true)
            return [];

        $bankId = 0;
        $bankIdSql = "and Cbin.BankId = :bankId";
        if ($Reservation["InstallmentVirtualPosId"]=="4"){
            $bankId = 10;
        }else if ($Reservation["InstallmentVirtualPosId"]=="5" ){
            $bankId = 9;
        }else if ($Reservation["InstallmentVirtualPosId"]=="6" ){
            $bankId = 8;
        }
        if($Reservation["InstallmentVirtualPosId"]=="0"){
            $bankId=0;
            $bankIdSql="";
        }

        $query = $db->prepare("select CI.Installment as installmentNumber,CBank.VirtualPosId,
       :Price3+cast((cast(:Price2 as decimal(10,2))/100*CI.Rate) as decimal(10,2)) as totalPrice,
       cast((:Price4+cast((cast(:Price5 as decimal(10,2))/100*CI.Rate) as decimal(10,2)))/CI.Installment as decimal(10,2)) as installmentPrice from CardBin Cbin 
                inner join dbo.CardBank CBank on CBank.ID=Cbin.BankId
               inner join dbo.CardInstallment CI on CI.BankCode=CBank.BankCode
              where Cbin.Bin = :bin ".$bankIdSql." and CI.uygula in(0,:tur) and CI.min_price<=:Price order by CI.Installment");

        if(post("tur")){
            $Reservation["tur"] = post("tur");
            if($Reservation["tur"]=="1"){
                $Reservation["Price"] = $Reservation["on_odeme"];
            }else{
                $Reservation["Price"] = $Reservation["toplam_tutar"];
            }
        }

        $ExecuteArr = [
            "bin"=>$Bin,
            "tur"=>$Reservation["tur"],
            "Price"=>$Reservation["Price"],
            "Price2"=>$Reservation["Price"],
            "Price3"=>$Reservation["Price"],
            "Price4"=>$Reservation["Price"],
            "Price5"=>$Reservation["Price"],
        ];
        if($bankId>0){
            $ExecuteArr["bankId"]=$bankId;
        }

        $query->execute($ExecuteArr);
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }
}