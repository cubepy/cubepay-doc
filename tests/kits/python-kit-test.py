import sys, uuid, unittest
from pathlib import Path
sys.path.insert(0, str(Path(__file__).resolve().parents[2]/'integrations/kits/python'))
from cubepay import CubePay, CubePayError, toman_to_rial
from store import Store

A='a'*50
class KitTests(unittest.TestCase):
    def test_units(self):
        self.assertEqual(toman_to_rial(20000),200000)
        for n in [True,0,-1,1.5,'100',900719925474100]:
            with self.assertRaises(ValueError): toman_to_rial(n)
    def test_contract(self):
        for code,body,state in [(402,{'success':False,'status':'pending'},'pending'),(410,{'success':False,'status':'expired'},'unpaid'),(409,{'success':False,'status':'verified'},'reconcile'),(200,{'success':True,'status':'verified','order_id':'o','amount':202000},'verified')]:
            c=CubePay('test',lambda *args:(code,body))
            self.assertEqual(c.verify(A,'o',200000,202100)['state'],state)
        for body in [{'success':True,'status':'verified','order_id':'other','amount':200000},{'success':True,'status':'verified','order_id':'o','amount':202101},{'success':True,'status':'verified','order_id':'o','amount':'200000'},{'success':False,'status':'verified','order_id':'o','amount':200000}]:
            with self.assertRaises(CubePayError): CubePay('test',lambda *a:(200,body)).verify(A,'o',200000,202100)
    def flow(self,sandbox=False,verify_code=200):
        calls=[]
        def transport(endpoint,data):
            calls.append((endpoint,data))
            if endpoint.startswith('create'):
                return 200,{'success':True,'authority':A,'pay_amount':202100,'payment_link':'https://cubevps.ir/smspay/pay.php?authority='+A,'is_test':sandbox}
            return verify_code,{'success':verify_code==200,'status':'verified','order_id':'o','amount':202000}
        return CubePay('test',transport),calls
    def test_persistent_idempotency(self):
        client,calls=self.flow()
        database=Path(__file__).resolve().parent/('persistence-'+uuid.uuid4().hex+'.db')
        try:
            path=str(database);s=Store(path,client)
            s.create('o',200000,'buyer','https://store.example/callback','buyername')
            self.assertEqual(calls[0][1]['customer_user_id'],'buyer')
            self.assertEqual(s.callback(A),'paid');s.db.close()
            s=Store(path,client);self.assertEqual(s.callback(A),'paid')
            self.assertEqual(len(calls),2)
            row=s.db.execute('SELECT * FROM delivery_outbox').fetchone()
            self.assertEqual(row['buyer'],'buyer');self.assertEqual(row['amount_rial'],200000)
            self.assertEqual(s.callback('b'*50),'unknown');s.db.close()
        finally:
            if database.exists(): database.unlink()
    def test_sandbox_and_duplicate_remote(self):
        for sandbox,code,state in [(True,200,'sandbox_paid'),(False,409,'reconcile')]:
            c,_=self.flow(sandbox,code);s=Store(':memory:',c)
            s.create('o',200000,'buyer','https://store.example/callback');self.assertEqual(s.callback(A),state)
            self.assertEqual(s.db.execute('SELECT count(*) FROM delivery_outbox').fetchone()[0],0);s.db.close()
    def test_unknown_creation(self):
        def fail(*args):raise CubePayError('timeout')
        s=Store(':memory:',CubePay('test',fail))
        with self.assertRaises(CubePayError):s.create('o',200000,'b','https://store.example/callback')
        self.assertEqual(s.db.execute('SELECT state FROM orders').fetchone()[0],'reconcile')
        with self.assertRaises(Exception):s.create('o',200000,'b','https://store.example/callback')
        s.db.close()
if __name__=='__main__':unittest.main()
