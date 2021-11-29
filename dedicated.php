<?php
if (!defined("WHMCS"))
    die("This file cannot be accessed directly");

use Illuminate\Database\Capsule\Manager as Capsule;

class DediMaxDedicatedAPI
{

    public $apikey = '';
    public $apiid = '';
    public $apiurl = 'https://api3.dedimax.com/api/3.0/';

    public function __construct($apiid = '', $apikey = '')
    {
        $this->apiid = $apiid;
        $this->apikey = $apikey;
    }

    public function ServersLists()
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiurl . "dedicatedserver");
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization: Basic ' . base64_encode("$this->apiid:$this->apikey")
            )
        );
        $result = curl_exec($ch);
        curl_close($ch);
        if ($result) {
            $list = json_decode($result, true);
            return $list;
        } else {
            return [];
        }
    }

    public function OSList($id = '')
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiurl . "dedicatedserver/plan/$id/os");
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization: Basic ' . base64_encode("$this->apiid:$this->apikey")
            )
        );
        $result = curl_exec($ch);
        curl_close($ch);
        if ($result) {
            $list = json_decode($result, true);
            return $list;
        } else {
            return [];
        }
    }

    public function plans()
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiurl . "dedicatedserver/plan");
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization: Basic ' . base64_encode("$this->apiid:$this->apikey")
            )
        );
        $result = curl_exec($ch);
        curl_close($ch);
        if ($result) {
            $list = json_decode($result, true);
            return $list;
        } else {
            return [];
        }
    }

    public function Shutdown($id = '')
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiurl . "dedicatedserver/$id/shutdown");
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array('id' => $id)));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization: Basic ' . base64_encode("$this->apiid:$this->apikey")
            )
        );
        $result = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($httpcode == 200) {
            return 'success';
        } else if ($httpcode == 400) {
            return 'Bad request';
        } else if ($httpcode == 402) {
            return 'Params Not Found';
        } else if ($httpcode == 404) {
            return 'Resource Not Found';
        } else if ($httpcode == 406) {
            return 'Not enough credits';
        } else {
            return 'Invalid Request';
        }
    }

    public function reboot($id = '')
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiurl . "dedicatedserver/$id/reboot");
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array('id' => $id)));
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization: Basic ' . base64_encode("$this->apiid:$this->apikey")
            )
        );
        $result = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($httpcode == 200) {
            return 'success';
        } else if ($httpcode == 400) {
            return 'Bad request';
        } else if ($httpcode == 402) {
            return 'Params Not Found';
        } else if ($httpcode == 404) {
            return 'Resource Not Found';
        } else if ($httpcode == 406) {
            return 'Not enough credits';
        } else {
            return 'Invalid Request';
        }
    }

    public function terminate($id = '')
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiurl . "dedicatedserver/$id");
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization: Basic ' . base64_encode("$this->apiid:$this->apikey")
            )
        );
        $result = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($httpcode == 200) {
            return 'success';
        } else if ($httpcode == 400) {
            return 'Bad request';
        } else if ($httpcode == 402) {
            return 'Params Not Found';
        } else if ($httpcode == 404) {
            return 'Resource Not Found';
        } else if ($httpcode == 406) {
            return 'Not enough credits';
        } else {
            return 'Invalid Request';
        }
    }

    public function ChangeOS($id = '', $osid = '')
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiurl . "dedicatedserver/$id/setup");
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array('template' => $osid, 'id' => $id)));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization: Basic ' . base64_encode("$this->apiid:$this->apikey")
            )
        );
        $result = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($httpcode == 200) {
            $list = json_decode($result, true);
            return $list;
        } else if ($httpcode == 400) {
            return 'Bad request';
        } else if ($httpcode == 402) {
            return 'Params Not Found';
        } else if ($httpcode == 404) {
            return 'Resource Not Found';
        } else if ($httpcode == 406) {
            return 'Not enough credits';
        } else {
            return 'Invalid Request';
        }
    }

    public function Create($planid = '', $osid = '')
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiurl . "dedicatedserver");
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array('plan' => $planid, 'os' => $osid)));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization: Basic ' . base64_encode("$this->apiid:$this->apikey")
            )
        );
        $result = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($httpcode == 200) {
            $result2 = json_decode($result, TRUE);
            if (is_array($result2) && isset($result2['id'])) {
                return $result2;
            } else {
                return $result;
            }
        } else if ($httpcode == 400) {
            return 'Bad request';
        } else if ($httpcode == 402) {
            return 'Params Not Found';
        } else if ($httpcode == 404) {
            return 'Resource Not Found';
        } else if ($httpcode == 406) {
            return 'Not enough credits';
        } else {
            return 'Invalid request';
        }
    }

    public function getStatus($id = '')
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiurl . "dedicatedserver/$id");
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization: Basic ' . base64_encode("$this->apiid:$this->apikey")
            )
        );
        $result = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($httpcode == 200) {
            return json_decode($result, TRUE);
        } else {
            return ['status' => 'stopped'];
        }
    }
}
