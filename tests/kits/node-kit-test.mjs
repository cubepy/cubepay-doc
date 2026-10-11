import assert from 'node:assert/strict';
import {CubePay,tomanToRial} from '../../integrations/kits/node/cubepay.mjs';
import {Store} from '../../integrations/kits/node/store.mjs';
const A='a'.repeat(50); let checks=0;
const eq=(a,b)=>{assert.deepEqual(a,b);checks++;};
eq(tomanToRial(20000),200000);
for(const n of [0,-1,1.5,'100',900719925474100]){assert.throws(()=>tomanToRial(n));checks++;}
for(const [code,body,state] of [[402,{success:false,status:'pending'},'pending'],[410,{success:false,status:'expired'},'unpaid'],[409,{success:false,status:'verified'},'reconcile'],[200,{success:true,status:'verified',order_id:'o',amount:202000},'verified']]){
 const c=new CubePay('test',async()=>[code,body]);eq((await c.verify(A,'o',200000,202100)).state,state);
}
for(const body of [{success:true,status:'verified',order_id:'wrong',amount:200000},{success:true,status:'verified',order_id:'o',amount:202101},{success:true,status:'verified',order_id:'o',amount:'200000'},{success:false,status:'verified',order_id:'o',amount:200000}]){
 await assert.rejects(new CubePay('test',async()=>[200,body]).verify(A,'o',200000,202100));checks++;
}
for(const [sandbox,code,expected] of [[false,200,'paid'],[true,200,'sandbox_paid'],[false,409,'reconcile']]){
 let calls=0;const c=new CubePay('test',async(endpoint,data)=>{calls++;return endpoint.startsWith('create')?[200,{success:true,authority:A,pay_amount:202100,payment_link:'https://cubevps.ir/smspay/pay.php?authority='+A,is_test:sandbox}]:[code,{success:code===200,status:'verified',order_id:'o',amount:202000}];});
 const s=new Store(':memory:',c);await s.create('o',200000,'buyer','https://store.example/callback');
 eq(await s.callback(A),expected);eq(await s.callback(A),expected);eq(calls,2);
 eq(s.db.prepare('SELECT count(*) as n FROM delivery_outbox').get().n,expected==='paid'?1:0);
 if(expected==='paid')eq(s.db.prepare('SELECT amount_rial FROM delivery_outbox').get().amount_rial,200000);
 eq(await s.callback('b'.repeat(50)),'unknown');s.db.close();
}
console.log(JSON.stringify({node_checks:checks}));
