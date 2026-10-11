<?php
declare(strict_types=1);
require __DIR__.'/../../integrations/kits/php/Store.php';
use CubePayKit\CubePay;use CubePayKit\Store;
$n=0;function kitcheck(bool $x,string $name):void{global $n;if(!$x)throw new RuntimeException($name);$n++;}
$a=str_repeat('a',50);kitcheck(CubePay::tomanToRial(20000)===200000,'unit');
foreach([[402,['success'=>false,'status'=>'pending'],'pending'],[410,['success'=>false,'status'=>'expired'],'unpaid'],[409,['success'=>false,'status'=>'verified'],'reconcile'],[200,['success'=>true,'status'=>'verified','order_id'=>'o','amount'=>202000],'verified']] as [$code,$body,$state]){
 $c=new CubePay('test',fn()=>[$code,$body]);kitcheck($c->verify($a,'o',200000,202100)['state']===$state,'verify '.$code);
}
foreach([['success'=>true,'status'=>'verified','order_id'=>'wrong','amount'=>200000],['success'=>true,'status'=>'verified','order_id'=>'o','amount'=>202101],['success'=>true,'status'=>'verified','order_id'=>'o','amount'=>'200000'],['success'=>false,'status'=>'verified','order_id'=>'o','amount'=>200000]] as $body){
 $c=new CubePay('test',fn()=>[200,$body]);$thrown=false;try{$c->verify($a,'o',200000,202100);}catch(Throwable $e){$thrown=true;}kitcheck($thrown,'reject invalid');
}
foreach([[false,200,'paid'],[true,200,'sandbox_paid'],[false,409,'reconcile']] as [$sandbox,$code,$expected]){
 $calls=0;$c=new CubePay('test',function($ep,$data)use(&$calls,$a,$sandbox,$code){$calls++;return str_starts_with($ep,'create')?[200,['success'=>true,'authority'=>$a,'pay_amount'=>202100,'payment_link'=>'https://cubevps.ir/smspay/pay.php?authority='.$a,'is_test'=>$sandbox]]:[$code,['success'=>$code===200,'status'=>'verified','order_id'=>'o','amount'=>202000]];});
 $s=new Store(':memory:',$c);$s->create('o',200000,'buyer','https://store.example/callback');
 kitcheck($s->callback($a)===$expected,'callback');kitcheck($s->callback($a)===$expected,'duplicate');kitcheck($calls===2,'one verify');
 kitcheck((int)$s->db->query('SELECT COUNT(*) FROM delivery_outbox')->fetchColumn()===($expected==='paid'?1:0),'outbox');
 if($expected==='paid')kitcheck((int)$s->db->query('SELECT amount_rial FROM delivery_outbox')->fetchColumn()===200000,'local amount');
}
echo json_encode(['php_kit_checks'=>$n])."\n";
