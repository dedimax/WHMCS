<?php
if (!defined("WHMCS"))
    die("This file cannot be accessed directly");

use Illuminate\Database\Capsule\Manager as Capsule;

add_hook('DailyCronJob', 1, function($vars) {
    $item = Capsule::table('tblproducts')->where('servertype', 'dedimax')->where('configoption1', '<>', '')->where('configoption2', '<>', '')->first();
    if ($item) {
        if (!class_exists('DediMaxAPI')) {
            require __DIR__ . '/functions.php';
        }
        $api = new DediMaxAPI($item->configoption1, $item->configoption2);
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
});
