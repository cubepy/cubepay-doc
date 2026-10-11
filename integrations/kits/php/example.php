<?php
declare(strict_types=1);
// CLI only. Do not expose this create endpoint directly to shoppers.
if (PHP_SAPI!=='cli') { http_response_code(404); exit; }
require_once __DIR__.'/Store.php';
use CubePayKit\CubePay;use CubePayKit\Store;
$mode=$argv[1]??'';
if (!in_array($mode,['create','callback'],true) || !getenv('CUBEPAY_DB')) exit("Set CUBEPAY_DB and CUBEPAY_TOKEN; create ORDER RIAL BUYER CALLBACK_HTTPS | callback AUTHORITY\n");
$s=new Store((string)getenv('CUBEPAY_DB'),new CubePay((string)getenv('CUBEPAY_TOKEN')));
if ($mode==='create') {
    if (count($argv)!==6 || !ctype_digit($argv[3])) exit("create ORDER AMOUNT_RIAL BUYER CALLBACK_HTTPS\n");
    echo json_encode($s->create($argv[2],(int)$argv[3],$argv[4],$argv[5]),JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR)."\n";
} else {
    if (count($argv)!==3) exit("callback AUTHORITY\n");
    $state=$s->callback($argv[2]);echo json_encode(['success'=>in_array($state,['paid','sandbox_paid'],true),'state'=>$state])."\n";
}
