<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
function testGetFunction(string $source, string $name): string
{
    $tokens = token_get_all($source, TOKEN_PARSE);
    for ($i = 0, $n = count($tokens); $i < $n; $i++) {
        if (!is_array($tokens[$i]) || $tokens[$i][0] !== T_FUNCTION) continue;
        $j = $i + 1;
        while ($j < $n && is_array($tokens[$j]) && $tokens[$j][0] === T_WHITESPACE) $j++;
        if (!is_array($tokens[$j] ?? null) || $tokens[$j][0] !== T_STRING || $tokens[$j][1] !== $name) continue;
        $code = ''; $depth = 0; $body = false;
        for ($j = $i; $j < $n; $j++) {
            $token = $tokens[$j];
            $code .= is_array($token) ? $token[1] : $token;
            if ($token === '{' || (is_array($token) && in_array($token[0], [T_CURLY_OPEN, T_DOLLAR_OPEN_CURLY_BRACES], true))) { $depth++; $body = true; }
            if ($token === '}' && --$depth === 0 && $body) { return $code; }
        }
    }
    throw new RuntimeException('Production function not found: ' . $name);
}
function check(bool $condition, string $message): void
{
    if (!$condition) throw new RuntimeException($message);
}

$root=dirname(__DIR__);
foreach (['docs/examples/CubePayClient.php','en/docs/examples/CubePayClient.php'] as $i=>$path) {
    $method=testGetFunction(file_get_contents($root.'/'.$path),'createPayment');
    $class='BuyerClientFixture'.$i;
    eval('class '.$class.' { '.$method.' private function request($method,$path,$data): array { return [$method,$path,$data]; } }');
    $client=new $class();
    $r=$client->createPayment(500000,'order','https://shop.example/callback',null,'7000000123','@Buyer_name');
    check($r[0]==='POST' && $r[1]==='/api/create-payment.php','SDK endpoint preserved');
    check($r[2]['customer_user_id']==='7000000123' && $r[2]['customer_username']==='Buyer_name','SDK buyer fields');
    check($r[2]['amount']===500000 && $r[2]['order_id']==='order' && $r[2]['callback_url']==='https://shop.example/callback','SDK financial fields preserved');
    $r=$client->createPayment(500000,'legacy','https://shop.example/callback');check(!isset($r[2]['customer_user_id']) && !isset($r[2]['customer_username']),'SDK legacy calls preserved');
}
define('BRIDGE_API_KEY','fixture-key');define('CUBEPAY_TOKEN','fixture-token');define('CUBEPAY_PAY_BASE','https://cubevps.ir/pay');
$state=['calls'=>[],'stored'=>[]];
function cg_self_url(){return 'https://shop.example/bridge.php';}
function cg_post($url,$payload,$token){$GLOBALS['state']['calls'][]=[$url,$payload,$token];return ['success'=>true,'pay_page_url'=>'https://pay.example/order','authority'=>'fixture'];}
function cg_store($id,$data){$GLOBALS['state']['stored'][]=[$id,$data];}
function cg_maybe_send_card($id,$res){}
function cg_log($msg){}
$source=file_get_contents($root.'/integrations/mirzabot-custom-gateway/cubepay-gateway.php');
eval(testGetFunction($source,'cg_buyer_metadata'));eval(testGetFunction($source,'handle_create'));
ob_start();handle_create(['api_key'=>'bad','order_id'=>'order','amount'=>50000,'user_id'=>'7000000123']);ob_end_clean();check(count($state['calls'])===0,'Unauthenticated metadata must not reach gateway');
ob_start();handle_create(['api_key'=>'fixture-key','order_id'=>'order','amount'=>50000,'user_id'=>'7000000123','customer_username'=>'@Buyer_name']);$out=json_decode(ob_get_clean(),true);
$r=end($state['calls']);$p=$r[1];check($p['customer_user_id']==='7000000123' && $p['customer_username']==='Buyer_name','Bridge forwards authenticated buyer');
check($p['price_amount']===50000 && $p['order_id']==='order' && $p['callback_url']==='https://shop.example/bridge.php?cb=1' && $p['redirect_after_payment']===false,'Bridge payment fields preserved');
check($r[0]==='https://cubevps.ir/pay/create-order.php' && $r[2]==='fixture-token' && $out['url']==='https://pay.example/order','Bridge auth, endpoint and output preserved');
check(cg_buyer_metadata(['user_id'=>'7000000456'])===['customer_user_id'=>'7000000456'],'No username required; buyer isolation');
foreach ([null,'','0',-1,[],true,1.5] as $id) check(cg_buyer_metadata(['user_id'=>$id])===[],'Invalid IDs omitted');
foreach (['none','NULL','<script>',null] as $name) check(cg_buyer_metadata(['user_id'=>'123','customer_username'=>$name])===['customer_user_id'=>'123'],'Invalid names omitted');
echo "PASS: PHP SDK compatibility and authenticated Mirzabot bridge buyer identity.\n";
