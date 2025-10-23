<?php


$siteCode="1";

$ajax_id = 0;
if( is_numeric(post("id")))
    $ajax_id = post("id");

$ajax_type = 0;
if( is_numeric(post("type")))
    $ajax_type = post("type");



$ajax_referer="";
$ajax_referer_paramaters="";
if(post("referer")){
    $ajax_referer = str_replace("'","",post("referer"));
    if(strpos($ajax_referer,"?")>-1){
        $srs=explode("?",$ajax_referer);
        $ajax_referer=$srs[0];
        $ajax_referer_paramaters=$srs[1];
    }
}

$server_path = str_replace("'","",$_SERVER['HTTP_REFERER']);
if(post("url")!="")
    $server_path = str_replace("'","",post("url"));

$server_paramaters="";
if(strpos($server_path,"?")>-1){
    $srs=explode("?",$server_path);
    $server_path=$srs[0];
    $server_paramaters=$srs[1];
}
$server_ip = $_SERVER['REMOTE_ADDR'];
if(isset($_SERVER['HTTP_CF_CONNECTING_IP'])&& $_SERVER['HTTP_CF_CONNECTING_IP']!="")
    $server_ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
$server_userAgent = $_SERVER['HTTP_USER_AGENT'];

    if(post("save")==true){

        $query = $db->prepare("select * from analytics_now where siteCode=:siteCode and last24HourDate>=getdate() and userAgent=:userAgent and userIp=:userIp and path=:path ");
        $query->execute([
            "siteCode" => $siteCode,
            "userAgent" => $server_userAgent,
            "userIp" => $server_ip,
            "path" => $server_path
        ]);
        $user_analytics_data = $query->fetch(PDO::FETCH_ASSOC);
        if($user_analytics_data){
            $query = $db->prepare("update dbo.analytics_now set paramaters=:paramaters where id=:id");
            $query->execute([
                "paramaters" => $server_paramaters,
                "id" => $user_analytics_data["id"]
            ]);
        }
        else{
            $query = $db->prepare("insert into dbo.analytics_now([siteCode],[refererUrl],[refererParamaters],[userAgent],[userIp],[path],[paramaters],[pageType],[pageId])
									values(:siteCode,:refererUrl,:refererParamaters,:userAgent,:userIp,:path,:paramaters,:pageType,:pageId)");
            $query->execute([
                "siteCode" => $siteCode,
                "refererUrl" => $ajax_referer,
                "refererParamaters" => $ajax_referer_paramaters,
                "userAgent" => $server_userAgent,
                "userIp" => $server_ip,
                "path" => $server_path,
                "paramaters" => $server_paramaters,
                "pageType" => $ajax_type,
                "pageId" => $ajax_id
            ]);

        }
    }
    if(post("data") && post("data")!=""){
        $ExecuteArr=[];
        if($ajax_type!=0 && $ajax_id!=0){
            $liveQuery="";
            $last24Query = "";



            if(strpos(post("data"),"live")>-1){
                $liveQuery= "
									(select COUNT(id) from analytics_now 
									where siteCode=:liveSiteCode and expiryDate>=getdate()
									and pageType=:livePageType and pageId=:livePageId) as live_count,";
                $ExecuteArr["liveSiteCode"] = $siteCode;
                $ExecuteArr["livePageType"] = $ajax_type;
                $ExecuteArr["livePageId"] = $ajax_id;
            }
            if(strpos(post("data"),"last24")>-1){
                $last24Query = "(select COUNT(id) from analytics_now 
									where siteCode=:last24SiteCode and last24HourDate>=getdate()
									and pageType=:last24PageType and pageId=:last24PageId) as last24_count,";
                $ExecuteArr["last24SiteCode"] = $siteCode;
                $ExecuteArr["last24PageType"] = $ajax_type;
                $ExecuteArr["last24PageId"] = $ajax_id;

            }

            $returndata_Query="select 
                ".$liveQuery."
                ".$last24Query."
                '' as blank";
        }
        else{
            $liveQuery="";
            $last24Query = "";

            if(strpos(post("data"),"live")>-1){
                $liveQuery= "(select COUNT(id) from analytics_now 
									where siteCode=:liveSiteCode and expiryDate>=getdate()
									and path=:livePath) as live_count,";
                $ExecuteArr["liveSiteCode"] = $siteCode;
                $ExecuteArr["livePath"] = $server_path;
            }
            if(strpos(post("data"),"last24")>-1){
                $last24Query = "(select COUNT(id) from analytics_now 
									where siteCode=:last24SiteCode and last24HourDate>=getdate()
									and path=:last24Path) as last24_count,";
                $ExecuteArr["last24SiteCode"] = $siteCode;
                $ExecuteArr["last24Path"] = $server_path;

            }
            $returndata_Query="select 
                ".$liveQuery."
                ".$last24Query."
                '' as blank";


        }
        $query = $db->prepare($returndata_Query);
        $query->execute($ExecuteArr);
        $returndata_Data = $query->fetch(PDO::FETCH_ASSOC);
        if($returndata_Data){
            $data["status"]=true;
            if($returndata_Data["live_count"]!=null)
                $data["data"]["live"]=$returndata_Data["live_count"];

            if($returndata_Data["last24_count"]!=null)
                $data["data"]["last24"]=$returndata_Data["last24_count"];
        }
    }



echo json_encode($data);