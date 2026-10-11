"""CLI integration example. Import Store into your authenticated shop checkout and callback routes."""
import os, sys, json
from cubepay import CubePay
from store import Store

def main():
    if len(sys.argv) < 3 or sys.argv[1] not in ('create', 'callback'):
        raise SystemExit('create ORDER AMOUNT_RIAL BUYER CALLBACK_HTTPS | callback AUTHORITY')
    s = Store(os.environ['CUBEPAY_DB'], CubePay(os.environ['CUBEPAY_TOKEN']))
    try:
        if sys.argv[1] == 'create':
            if len(sys.argv) != 6: raise SystemExit('create ORDER AMOUNT_RIAL BUYER CALLBACK_HTTPS')
            print(json.dumps(s.create(sys.argv[2], int(sys.argv[3]), sys.argv[4], sys.argv[5]), ensure_ascii=False))
        else:
            state = s.callback(sys.argv[2])
            print(json.dumps({'success': state in ('paid','sandbox_paid'), 'state':state}))
    finally:
        s.db.close()

if __name__ == '__main__': main()
