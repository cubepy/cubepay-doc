<?php
declare(strict_types=1);
namespace CubePayKit;
require_once __DIR__.'/CubePay.php';
final class Store {
    public \PDO $db;
    public function __construct(string $privateDatabasePath,private CubePay $client) {
        $this->db=new \PDO('sqlite:'.$privateDatabasePath,null,null,[\PDO::ATTR_ERRMODE=>\PDO::ERRMODE_EXCEPTION]);
        $this->db->exec('PRAGMA busy_timeout=10000');
        $this->db->exec(file_get_contents(__DIR__.'/schema.sql'));
    }
    private function run(string $sql,array $args=[]): \PDOStatement {
        $q=$this->db->prepare($sql); $q->execute($args); return $q;
    }
    public function create(string $order,int $amountRial,string $buyer,string $callback,?string $username=null): array {
        $this->run("INSERT INTO orders(order_id,requested,buyer,state) VALUES (?,?,?,'creating')",[$order,$amountRial,$buyer]);
        try {
            $r=$this->client->create($order,$amountRial,$callback,$buyer,$username);
            if (!is_bool($r['is_test']??null)) throw new \RuntimeException('Sandbox identity missing; reconcile creation');
            $this->run("UPDATE orders SET authority=?,payable=?,payment_link=?,sandbox=?,state='waiting' WHERE order_id=?",[$r['authority'],$r['pay_amount'],$r['payment_link'],(int)$r['is_test'],$order]);
            return $r;
        } catch (\Throwable $e) { $this->run("UPDATE orders SET state='reconcile' WHERE order_id=?",[$order]); throw $e; }
    }
    public function callback(string $authority): string {
        $this->db->exec('BEGIN IMMEDIATE');
        try {
            $r=$this->run('SELECT * FROM orders WHERE authority=?',[$authority])->fetch(\PDO::FETCH_ASSOC);
            if (!$r || !in_array($r['state'],['waiting','unpaid'],true)) { $this->db->exec('COMMIT'); return $r['state']??'unknown'; }
            $this->run("UPDATE orders SET state='verifying' WHERE order_id=?",[$r['order_id']]); $this->db->exec('COMMIT');
        } catch (\Throwable $e) { $this->db->exec('ROLLBACK'); throw $e; }
        try { $v=$this->client->verify($authority,$r['order_id'],(int)$r['requested'],(int)$r['payable']); }
        catch (\Throwable $e) { $this->run("UPDATE orders SET state='reconcile' WHERE order_id=?",[$r['order_id']]); throw $e; }
        $state=['pending'=>'waiting','unpaid'=>'unpaid','reconcile'=>'reconcile','verified'=>'paid'][$v['state']];
        if ($state==='paid' && (int)$r['sandbox']===1) $state='sandbox_paid';
        $this->db->exec('BEGIN IMMEDIATE');
        try {
            $this->run('UPDATE orders SET state=?,verified_amount=? WHERE order_id=?',[$state,$v['amount_rial']??null,$r['order_id']]);
            if ($state==='paid') $this->run('INSERT INTO delivery_outbox(order_id,authority,buyer,amount_rial) VALUES (?,?,?,?)',[$r['order_id'],$authority,$r['buyer'],$r['requested']]);
            $this->db->exec('COMMIT');
        } catch (\Throwable $e) { $this->db->exec('ROLLBACK'); throw $e; }
        return $state;
    }
}
