<?php



class SmsService
{

    private  $Username;
    private  $Password;
    private  $Org;
    private  $Receiver = [];
    private  $Message;
    private $Service = "NetGsm";
    public $ErrMsg;

    public function __construct()
    {
        global $qsql;

        $this->Username = $qsql["smska"];
        $this->Password = $qsql["smspwd"];
        $this->Org = $qsql["smsorg"];
    }

    public function GetBalance(){
        $run = (new $this->Service)->GetBalanceService();
        if ($run["error"])
            $this->ErrMsg = $run["error"];
        else
            return $run["response"];

        return 0;
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->Message;
    }

    /**
     * @return false|mixed
     */
    public function getOrg()
    {
        return $this->Org;
    }

    /**
     * @return false|mixed
     */
    public function getPassword()
    {
        return $this->Password;
    }

    /**
     * @return array
     */
    public function getReceiver(): array
    {
        return $this->Receiver;
    }

    /**
     * @return false|mixed
     */
    public function getUsername()
    {
        return $this->Username;
    }


    /**
     * @param mixed $Message
     */
    public function setMessage($Message): void
    {
        $this->Message = $Message;
    }

    /**
     * @param array $Receiver
     */
    public function setReceiver(array $Receiver): void
    {
        $this->Receiver = $Receiver;
    }


}