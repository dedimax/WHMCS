<?php
if (!defined("WHMCS"))
    die("This file cannot be accessed directly");

use Illuminate\Database\Capsule\Manager as Capsule;

function dedimax_ConfigOptions()
{
    if (!Capsule::schema()->hasTable('mod_dedimax_servers')) {
        Capsule::schema()->create('mod_dedimax_servers', function ($table) {
            $table->increments('id');
            $table->integer('serviceid');
            $table->integer('vpsid');
        });
    }
    if (!Capsule::schema()->hasTable('mod_dedimax_dservers')) {
        Capsule::schema()->create('mod_dedimax_dservers', function ($table) {
            $table->increments('id');
            $table->integer('serviceid');
            $table->integer('serverid');
        });
    }
    if (defined('ADMINAREA') && isset($_REQUEST['action']) && $_REQUEST['action'] == 'module-settings' && $_REQUEST['module'] == 'dedimax') {
        $item = Capsule::table('tblproducts')->find($_REQUEST['id']);
        if ($item->configoption1 != '' && $item->configoption2 != '') {
            if (!class_exists('DediMaxCloudAPI')) {
                require __DIR__ . '/functions.php';
            }
            if (!class_exists('DediMaxDedicatedAPI')) {
                require __DIR__ . '/dedicated.php';
            }
            if ($item->configoption3 == 'Cloud') {
                $api = new DediMaxCloudAPI($item->configoption1, $item->configoption2);
                $plans = $api->plans();
                $locations = $api->locations();
                $l = '';
                $p = '';
                foreach ($plans as $plan) {
                    $p .= $plan['id'] . ' - ' . $plan['name'] . ',';
                }
                foreach ($locations as $location) {
                    $location['city'] = str_replace(',', ' -', $location['city']);
                    $l .= $location['id'] . ' - ' . $location['city'] . ',';
                }
                $p = rtrim($p, ',');
                $l = rtrim($l, ',');
                $oslist = \WHMCS\Config\Setting::getValue("dedimaxoslist");
                if (!$oslist) {
                    $oslist = $api->OSList();
                    if (count($oslist)) {
                        Capsule::table('tblconfiguration')->updateOrInsert([
                            'setting' => 'dedimaxoslist',
                            ], [
                            'setting' => 'dedimaxoslist',
                            'value' => serialize($oslist),
                        ]);
                    }
                }
                $configarray = array(
                    'API_ID' => array("Type" => "text", "Size" => "30", "Default" => "", "Description" => ''),
                    'API_KEY' => array("Type" => "text", "Size" => "30", "Default" => "", "Description" => ''),
                    'Type' => array("Type" => "dropdown", "Size" => "30", "Options" => 'Cloud,Dedicated Server', "Default" => "", "Description" => ''),
                    'Plan' => array("Type" => "dropdown", "Size" => "30", "Options" => $p, "Default" => "", "Description" => ''),
                    'Location' => array("Type" => "dropdown", "Size" => "30", "Options" => $l, "Default" => "", "Description" => ''),
                );
            } else {
                $api = new DediMaxDedicatedAPI($item->configoption1, $item->configoption2);
                $plans = $api->plans();
                $existing = [];
                foreach ($plans as $pkey => $pvalue) {
                    //if (!$pvalue['available']) {
                    $existing[] = $pvalue;
                    //}
                }
                $p = '';
                foreach ($existing as $plan) {
                    $plan['location'] = str_replace(',', '_', $plan['location']);
                    $p .= $plan['id'] . ' - ' . $plan['location'] . ' - ' . $plan['cpu'] . ' - ' . $plan['ram'] . ',';
                }
                $p = rtrim($p, ',');
                $configarray = array(
                    'API_ID' => array("Type" => "text", "Size" => "30", "Default" => "", "Description" => ''),
                    'API_KEY' => array("Type" => "text", "Size" => "30", "Default" => "", "Description" => ''),
                    'Type' => array("Type" => "dropdown", "Size" => "30", "Options" => 'Cloud,Dedicated Server', "Default" => "", "Description" => ''),
                    'Plan' => array("Type" => "dropdown", "Size" => "30", "Options" => $p, "Default" => "", "Description" => ''),
                );
            }
        } else {
            $configarray = array(
                'API_ID' => array("Type" => "text", "Size" => "30", "Default" => "", "Description" => ''),
                'API_KEY' => array("Type" => "text", "Size" => "30", "Default" => "", "Description" => ''),
                'Type' => array("Type" => "dropdown", "Size" => "30", "Options" => 'Cloud,Dedicated Server', "Default" => "", "Description" => ''),
            );
        }
    } else {
        $configarray = array(
            'API_ID' => array("Type" => "text", "Size" => "30", "Default" => "", "Description" => ''),
            'API_KEY' => array("Type" => "text", "Size" => "30", "Default" => "", "Description" => ''),
            'Type' => array("Type" => "dropdown", "Size" => "30", "Options" => 'Cloud,Dedicated Server', "Default" => "", "Description" => ''),
            'Plan' => array("Type" => "dropdown", "Size" => "30", "Options" => "", "Default" => "", "Description" => ''),
            'Location' => array("Type" => "dropdown", "Size" => "30", "Options" => "", "Default" => "", "Description" => ''),
        );
    }
    return $configarray;
}

function dedimax_CreateAccount($params)
{
    if (!class_exists('DediMaxCloudAPI')) {
        require __DIR__ . '/functions.php';
    }
    if (!class_exists('DediMaxDedicatedAPI')) {
        require __DIR__ . '/dedicated.php';
    }
    if ($params['configoption3'] != 'Cloud') {
        $api = new DediMaxDedicatedAPI($params['configoption1'], $params['configoption2']);
        $plan = explode('-', $params['configoption4'])[0];
        $plan = trim($plan);
        $oslist = $api->OSList($plan);
        $osid = 0;
        foreach ($oslist as $os) {
            if (strtolower($params["configoptions"]["Operating System"]) == strtolower($os['name'])) {
                $osid = $os['id'];
            }
        }
        if ($osid <= 0) {
            return 'Error: Invalid Operating System!';
        }
        $result = $api->Create($plan, $osid);
    } else {
        $api = new DediMaxCloudAPI($params['configoption1'], $params['configoption2']);
        if (isset($params["configoptions"]["Operating System"])) {
            $params["os"] = $params["configoptions"]["Operating System"];
        }
        $plan = explode('-', $params['configoption4'])[0];
        $location = explode('-', $params['configoption5'])[0];
        $oslist = \WHMCS\Config\Setting::getValue("dedimaxoslist");
        if (!$oslist) {
            $oslist = $api->OSList();
            if (count($oslist)) {
                Capsule::table('tblconfiguration')->updateOrInsert([
                    'setting' => 'dedimaxoslist',
                    ], [
                    'setting' => 'dedimaxoslist',
                    'value' => serialize($oslist),
                ]);
            }
        }
        $oslist = unserialize($oslist);
        $osid = 0;
        foreach ($oslist as $os) {
            $fullname = strtolower($os['name'] . ' ' . $os['version']);
            $fullname1 = strtolower($os['name'] . ' - ' . $os['version']);
            if (strtolower($params["os"]) == $fullname || strtolower($params["os"]) == $fullname1) {
                $osid = $os['id'];
            }
        }
        if ($osid <= 0) {
            return 'Error: Invalid Operating System!';
        }
        $result = $api->Create($plan, $location, $osid);
    }
    if (is_array($result)) {
        if (isset($result['password'])) {
            Capsule::table('tblhosting')->where('id', $params['serviceid'])->update([
                'dedicatedip' => isset($result['IP']) ? $result['IP'] : '',
                'username' => isset($result['login']) ? $result['login'] : '',
                'password' => encrypt($result['password']),
            ]);
        }
        if ($params['configoption3'] != 'Cloud') {
            Capsule::table('mod_dedimax_dservers')->updateOrInsert([
                'serviceid' => $params['serviceid'],
                ], [
                'serviceid' => $params['serviceid'],
                'serverid' => $result['id'],
            ]);
            return 'Request is sent, you need to fill information when server is ready in your panel.';			
        } else {
            Capsule::table('mod_dedimax_servers')->updateOrInsert([
                'serviceid' => $params['serviceid'],
                ], [
                'serviceid' => $params['serviceid'],
                'vpsid' => $result['id'],
            ]);
        }
        return 'success';
    } elseif (is_numeric($result)) {
        Capsule::table('mod_dedimax_servers')->updateOrInsert([
            'serviceid' => $params['serviceid'],
            ], [
            'serviceid' => $params['serviceid'],
            'vpsid' => $result,
        ]);
        return 'success';
    } else {
        return $result;
    }
}

function dedimax_TerminateAccount($params)
{
    if ($params['configoption3'] != 'Cloud') {
        $item = Capsule::table('mod_dedimax_dservers')->where('serviceid', $params['serviceid'])->first();
        if (!$item) {
            return 'Invalid Request, Server Not Found';
        }
        if (!class_exists('DediMaxDedicatedAPI')) {
            require __DIR__ . '/dedicated.php';
        }
        $api = new DediMaxDedicatedAPI($params['configoption1'], $params['configoption2']);
        $status = $api->terminate($item->serverid);
    } else {
        $item = Capsule::table('mod_dedimax_servers')->where('serviceid', $params['serviceid'])->first();
        if (!$item) {
            return 'Invalid Request, Server Not Found';
        }
        if (!class_exists('DediMaxCloudAPI')) {
            require __DIR__ . '/functions.php';
        }
        $api = new DediMaxCloudAPI($params['configoption1'], $params['configoption2']);
        $status = $api->terminate($item->vpsid);
    }
    return $status;
}

function dedimax_SuspendAccount($params)
{
    if ($params['configoption3'] != 'Cloud') {
        $item = Capsule::table('mod_dedimax_dservers')->where('serviceid', $params['serviceid'])->first();
        if (!$item) {
            return 'Invalid Request, Server Not Found';
        }
        if (!class_exists('DediMaxDedicatedAPI')) {
            require __DIR__ . '/dedicated.php';
        }
        $api = new DediMaxDedicatedAPI($params['configoption1'], $params['configoption2']);
        $status = $api->Shutdown($item->serverid);
    } else {
        $item = Capsule::table('mod_dedimax_servers')->where('serviceid', $params['serviceid'])->first();
        if (!$item) {
            return 'Invalid Request, Server Not Found';
        }
        if (!class_exists('DediMaxCloudAPI')) {
            require __DIR__ . '/functions.php';
        }
        $api = new DediMaxCloudAPI($params['configoption1'], $params['configoption2']);
        $status = $api->Shutdown($item->vpsid);
    }
    return $status;
}

function dedimax_UnsuspendAccount($params)
{
    if ($params['configoption3'] != 'Cloud') {
        $item = Capsule::table('mod_dedimax_dservers')->where('serviceid', $params['serviceid'])->first();
        if (!$item) {
            return 'Invalid Request, Server Not Found';
        }
        if (!class_exists('DediMaxDedicatedAPI')) {
            require __DIR__ . '/dedicated.php';
        }
        $api = new DediMaxDedicatedAPI($params['configoption1'], $params['configoption2']);
        $status = $api->reboot($item->serverid);
    } else {
        $item = Capsule::table('mod_dedimax_servers')->where('serviceid', $params['serviceid'])->first();
        if (!$item) {
            return 'Invalid Request, Server Not Found';
        }
        if (!class_exists('DediMaxCloudAPI')) {
            require __DIR__ . '/functions.php';
        }
        $api = new DediMaxCloudAPI($params['configoption1'], $params['configoption2']);
        $status = $api->reboot($item->vpsid);
    }
    return $status;
}

function dedimax_AdminCustomButtonArray($params = [])
{
    $item = Capsule::table('mod_dedimax_servers')->where('serviceid', $params['serviceid'])->first();
    if (!$item) {
        return [];
    }
    return array(
        "Reboot Server" => "restart",
        "Start Server" => "start",
        "Shutdown Server" => "shutdown",
    );
}

function dedimax_start($params = array())
{
    if ($params['configoption3'] != 'Cloud') {
        $item = Capsule::table('mod_dedimax_dservers')->where('serviceid', $params['serviceid'])->first();
        if (!$item) {
            return 'Invalid Request, Server Not Found';
        }
        if (!class_exists('DediMaxDedicatedAPI')) {
            require __DIR__ . '/dedicated.php';
        }
        $api = new DediMaxDedicatedAPI($params['configoption1'], $params['configoption2']);
        $status = $api->reboot($item->serverid);
    } else {
        $item = Capsule::table('mod_dedimax_servers')->where('serviceid', $params['serviceid'])->first();
        if (!$item) {
            return 'Invalid Request, Server Not Found';
        }
        if (!class_exists('DediMaxCloudAPI')) {
            require __DIR__ . '/functions.php';
        }
        $api = new DediMaxCloudAPI($params['configoption1'], $params['configoption2']);
        $status = $api->reboot($item->vpsid);
    }
    return $status;
}

function dedimax_shutdown($params = array())
{
    if ($params['configoption3'] != 'Cloud') {
        $item = Capsule::table('mod_dedimax_dservers')->where('serviceid', $params['serviceid'])->first();
        if (!$item) {
            return 'Invalid Request, Server Not Found';
        }
        if (!class_exists('DediMaxDedicatedAPI')) {
            require __DIR__ . '/dedicated.php';
        }
        $api = new DediMaxDedicatedAPI($params['configoption1'], $params['configoption2']);
        $status = $api->Shutdown($item->serverid);
    } else {
        $item = Capsule::table('mod_dedimax_servers')->where('serviceid', $params['serviceid'])->first();
        if (!$item) {
            return 'Invalid Request, Server Not Found';
        }
        if (!class_exists('DediMaxCloudAPI')) {
            require __DIR__ . '/functions.php';
        }
        $api = new DediMaxCloudAPI($params['configoption1'], $params['configoption2']);
        $status = $api->Shutdown($item->vpsid);
    }
    return $status;
}

function dedimax_restart($params = array())
{
    if ($params['configoption3'] != 'Cloud') {
        $item = Capsule::table('mod_dedimax_dservers')->where('serviceid', $params['serviceid'])->first();
        if (!$item) {
            return 'Invalid Request, Server Not Found';
        }
        if (!class_exists('DediMaxDedicatedAPI')) {
            require __DIR__ . '/dedicated.php';
        }
        $api = new DediMaxDedicatedAPI($params['configoption1'], $params['configoption2']);
        $status = $api->reboot($item->vpsid);
    } else {
        $item = Capsule::table('mod_dedimax_servers')->where('serviceid', $params['serviceid'])->first();
        if (!$item) {
            return 'Invalid Request, Server Not Found';
        }
        if (!class_exists('DediMaxCloudAPI')) {
            require __DIR__ . '/functions.php';
        }
        $api = new DediMaxCloudAPI($params['configoption1'], $params['configoption2']);
        $status = $api->reboot($item->vpsid);
    }
    return $status;
}

function dedimax_ClientArea($params)
{
    $vars1 = [];
    $userid = $params['clientsdetails']['userid'];
    $serviceid = $params['serviceid'];
    $dbos = \WHMCS\Config\Setting::getValue("dedimaxoslist");
    $vars1['os'] = $params["configoptions"]["Operating System"];
    $vars1['ip'] = $params['model']->dedicatedip;
    $vars1['assignedips'] = $params['model']->assignedips;
    if ($dbos != '') {
        $oslist = unserialize($dbos);
    }
    $location = explode('-', $params['configoption5']);
    unset($location[0]);
    $vars1['location'] = implode(',', $location);
    $vars1['location'] = str_replace(' , ', ', ', $vars1['location']);
    if ($params['configoption3'] != 'Cloud') {
        $item = Capsule::table('mod_dedimax_dservers')->where('serviceid', $params['serviceid'])->first();
        if (!class_exists('DediMaxDedicatedAPI')) {
            require __DIR__ . '/dedicated.php';
        }
        $api = new DediMaxDedicatedAPI($params['configoption1'], $params['configoption2']);
        $status = $api->getStatus($item->serverid);
    } else {
        if (!class_exists('DediMaxCloudAPI')) {
            require __DIR__ . '/functions.php';
        }
        $api = new DediMaxCloudAPI($params['configoption1'], $params['configoption2']);
        $item = Capsule::table('mod_dedimax_servers')->where('serviceid', $params['serviceid'])->first();
        $status = $api->getStatus($item->vpsid);
    }
    $oslist = \WHMCS\Config\Setting::getValue("dedimaxoslist");
    if (!$oslist) {
        $oslist = $api->OSList();
        if (count($oslist)) {
            Capsule::table('tblconfiguration')->updateOrInsert([
                'setting' => 'dedimaxoslist',
                ], [
                'setting' => 'dedimaxoslist',
                'value' => serialize($oslist),
            ]);
        }
    } else {
        $oslist = unserialize($oslist);
    }
    return array(
        'templatefile' => ($params['configoption3'] != 'Cloud') ? 'templates/dedicated' : 'templates/cloud',
        'breadcrumb' => array('clientarea.php?action=productdetails&id=' . $serviceid . '&modop=custom&a=viewaccounts' => 'Server Management'),
        'vars' => array(
            'status' => $status,
            'params' => $params,
            'data' => $vars1,
            'oslist' => $oslist,
        ),
    );
}

function dedimax_ClientAreaCustomButtonArray()
{
    $buttonarray = array(
        'Manage' => "manage",
    );

    return $buttonarray;
}

function dedimax_manage($params = array())
{
    if (!isset($_POST['a']) && !isset($_POST['b'])) {
        redir('action=productdetails&id=' . $params['serviceid']);
        exit;
    }
    if ($params['configoption3'] != 'Cloud') {
        $item = Capsule::table('mod_dedimax_dservers')->where('serviceid', $params['serviceid'])->first();
        if (!$item) {
            return 'Invalid Request, Server Not Found';
        }
        if (!class_exists('DediMaxDedicatedAPI')) {
            require __DIR__ . '/dedicated.php';
        }
        $api = new DediMaxDedicatedAPI($params['configoption1'], $params['configoption2']);
        $serverid = $item->serverid;
    } else {
        $item = Capsule::table('mod_dedimax_servers')->where('serviceid', $params['serviceid'])->first();
        if (!$item) {
            return 'Invalid Request, Server Not Found';
        }
        if (!class_exists('DediMaxCloudAPI')) {
            require __DIR__ . '/functions.php';
        }
        $api = new DediMaxCloudAPI($params['configoption1'], $params['configoption2']);
        $serverid = $item->vpsid;
    }
    $arraypowers = ['stop', 'start', 'restart', 'shutdown'];
    if (in_array($_POST['b'], $arraypowers) && isset($_POST['s'])) {
        if ($_POST['b'] == 'shutdown' || $_POST['b'] == 'stop') {
            $result = $api->Shutdown($serverid);
        } else {
            $result = $api->reboot($serverid);
        }
        if ($result == 'success') {
            sleep(2);
            return 'success';
        }
        return 'Error in ' . $_POST['b'] . ' proccess';
    }
    if ($_POST['b'] == 'changeos') {
        $result = $api->ChangeOS($serverid, $_POST['osid']);
        if (is_array($result) && isset($result['login'])) {
            Capsule::table('tblhosting')->where('id', $params['serviceid'])->update([
                'username' => $result['login'],
                'password' => encrypt($result['password']),
            ]);
            sleep(4);
            return 'success';
        } else {
            return 'Error in ' . $_POST['b'] . ' proccess';
        }
    }
    return 'Invalid Request';
}
