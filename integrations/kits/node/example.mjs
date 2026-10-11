import {CubePay} from './cubepay.mjs';
import {Store} from './store.mjs';
const [command, ...args] = process.argv.slice(2);
if (!['create','callback'].includes(command) || !process.env.CUBEPAY_DB) throw new Error('Set CUBEPAY_DB and CUBEPAY_TOKEN; use create ORDER RIAL BUYER CALLBACK_HTTPS or callback AUTHORITY');
const store = new Store(process.env.CUBEPAY_DB, new CubePay(process.env.CUBEPAY_TOKEN));
try {
  if (command === 'create') {
    if (args.length !== 4) throw new Error('create ORDER AMOUNT_RIAL BUYER CALLBACK_HTTPS');
    console.log(JSON.stringify(await store.create(args[0], Number(args[1]), args[2], args[3])));
  } else {
    if (args.length !== 1) throw new Error('callback AUTHORITY');
    const state = await store.callback(args[0]); console.log(JSON.stringify({success:['paid','sandbox_paid'].includes(state),state}));
  }
} finally { store.db.close(); }
