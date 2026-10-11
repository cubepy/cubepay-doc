<?php
declare(strict_types=1);
namespace CubePayKit;
final class CubePay {
    private $transport;
    public function __construct(private string $token, ?callable $transport=null) {
        if ($token==='' || preg_match('/\s/',$token)) throw new \InvalidArgumentException('Set CUBEPAY_TOKEN on the server');
        $this->transport=$transport ?? [$this,'http'];
    }
    public static function tomanToRial(int $n): int {
        if ($n<1 || $n>900719925474099) throw new \InvalidArgumentException('Positive integer tomans required');
        return $n*10;
    }
    private function http(string $endpoint,array $data): array {
        $ch=curl_init('https://cubevps.ir/smspay/api/'.$endpoint); $body='';
        curl_setopt_array($ch,[CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>json_encode($data,JSON_THROW_ON_ERROR),
            CURLOPT_FOLLOWLOCATION=>false,CURLOPT_TIMEOUT=>15,CURLOPT_CONNECTTIMEOUT=>5,
            CURLOPT_PROTOCOLS=>CURLPROTO_HTTPS,CURLOPT_SSL_VERIFYPEER=>true,CURLOPT_SSL_VERIFYHOST=>2,
            CURLOPT_HTTPHEADER=>['Authorization: Bearer '.$this->token,'Content-Type: application/json','Accept: application/json'],
            CURLOPT_WRITEFUNCTION=>static function($ch,string $part) use (&$body): int {
                if (strlen($body)+strlen($part)>65536) return 0;
                $body.=$part; return strlen($part);
            }]);
        $ok=curl_exec($ch); $code=(int)curl_getinfo($ch,CURLINFO_HTTP_CODE); curl_close($ch);
        if ($ok===false) throw new \RuntimeException('Unknown network outcome; reconcile before retrying');
        $result=json_decode($body,true,512,JSON_THROW_ON_ERROR);
        if (!is_array($result) || array_is_list($result)) throw new \RuntimeException('Invalid API object');
        return [$code,$result];
    }
    public function create(string $orderId,int $amountRial,string $callbackUrl,?string $customerUserId=null,?string $customerUsername=null): array {
        $length=preg_match_all('/./us',$orderId);
        if (!$length || $length>64 || trim($orderId)!==$orderId || str_starts_with($orderId,'topup-')) throw new \InvalidArgumentException('Unique ordinary order ID required');
        if ($amountRial<1000 || $amountRial>9007199254740991) throw new \InvalidArgumentException('Integer RIAL amount >= 1000 required');
        if (!str_starts_with($callbackUrl,'https://') || strlen($callbackUrl)>500) throw new \InvalidArgumentException('Public HTTPS callback required');
        $data=['order_id'=>$orderId,'amount'=>$amountRial,'callback_url'=>$callbackUrl];
        if ($customerUserId!==null) $data['customer_user_id']=$customerUserId;
        if ($customerUsername!==null) $data['customer_username']=ltrim($customerUsername,'@');
        [$code,$r]=($this->transport)('create-payment.php',$data);
        if ($code!==200 || ($r['success']??null)!==true || !is_string($r['authority']??null)
            || !preg_match('/^[a-f0-9]{50}$/D',$r['authority']) || !is_int($r['pay_amount']??null)
            || $r['pay_amount']<$amountRial || $r['pay_amount']>9007199254740991
            || ($r['payment_link']??'')!=='https://cubevps.ir/smspay/pay.php?authority='.$r['authority']) throw new \RuntimeException('Creation not confirmed; preserve order and reconcile');
        return $r;
    }
    public function verify(string $authority,string $expectedOrderId,int $requestedRial,int $savedPayRial): array {
        if (!preg_match('/^[a-f0-9]{50}$/D',$authority) || $requestedRial<1000 || $savedPayRial<$requestedRial) throw new \InvalidArgumentException('Load valid authority and amounts from stored order');
        [$code,$r]=($this->transport)('verify-payment.php',['authority'=>$authority]);
        if ($code===402 && ($r['status']??'')==='pending' && ($r['success']??null)===false) return ['state'=>'pending'];
        if ($code===410 && ($r['success']??null)===false) return ['state'=>'unpaid'];
        if ($code===409 && ($r['status']??'')==='verified') return ['state'=>'reconcile'];
        if ($code!==200 || ($r['success']??null)!==true || ($r['status']??'')!=='verified'
            || ($r['order_id']??null)!==$expectedOrderId || !is_int($r['amount']??null)
            || $r['amount']<$requestedRial || $r['amount']>$savedPayRial) throw new \RuntimeException('Verification not bound to stored order; reconcile');
        return ['state'=>'verified','amount_rial'=>$r['amount']];
    }
}
