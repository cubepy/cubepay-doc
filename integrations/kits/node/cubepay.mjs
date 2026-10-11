// Node.js 22+; integer RIALS. No retries and no redirects carrying the API token.
export class CubePayError extends Error {}
export function tomanToRial(n) {
  if (!Number.isSafeInteger(n) || n < 1 || n > 900719925474099) throw new TypeError('Expected positive integer tomans');
  return n * 10;
}
export class CubePay {
  #token; #transport;
  constructor(token, transport) {
    if (typeof token !== 'string' || !token || /\s/.test(token)) throw new TypeError('Set CUBEPAY_TOKEN on the server');
    this.#token = token;
    this.#transport = transport ?? this.#http.bind(this);
  }
  async #http(endpoint, data) {
    try {
      const r = await fetch('https://cubevps.ir/smspay/api/' + endpoint, {
        method: 'POST', redirect: 'error', signal: AbortSignal.timeout(15000),
        headers: { Authorization: 'Bearer ' + this.#token, 'Content-Type': 'application/json', Accept: 'application/json' },
        body: JSON.stringify(data)
      });
      let text = ''; const reader = r.body.getReader(); const decoder = new TextDecoder(); let size = 0;
      for (;;) {
        const { value, done } = await reader.read(); if (done) break;
        size += value.byteLength;
        if (size > 65536) { await reader.cancel(); throw new Error('Response too large'); }
        text += decoder.decode(value, { stream: true });
      }
      text += decoder.decode(); const body = JSON.parse(text);
      if (!body || typeof body !== 'object' || Array.isArray(body)) throw new Error('Invalid response');
      return [r.status, body];
    } catch { throw new CubePayError('Unknown network/API outcome; reconcile before retrying'); }
  }
  async create(orderId, amountRial, callbackUrl, customerUserId, customerUsername) {
    if (typeof orderId !== 'string' || [...orderId].length < 1 || [...orderId].length > 64 || orderId.trim() !== orderId || orderId.startsWith('topup-')) throw new TypeError('Use a unique ordinary order ID');
    if (!Number.isSafeInteger(amountRial) || amountRial < 1000) throw new TypeError('Integer RIAL amount >= 1000 required');
    if (typeof callbackUrl !== 'string' || !callbackUrl.startsWith('https://') || callbackUrl.length > 500) throw new TypeError('Public HTTPS callback URL required');
    const data = { order_id: orderId, amount: amountRial, callback_url: callbackUrl };
    if (customerUserId != null) data.customer_user_id = String(customerUserId);
    if (customerUsername != null) data.customer_username = String(customerUsername).replace(/^@/, '');
    const [code, body] = await this.#transport('create-payment.php', data);
    if (code !== 200 || body.success !== true || typeof body.authority !== 'string' || !/^[a-f0-9]{50}$/.test(body.authority)
      || !Number.isSafeInteger(body.pay_amount) || body.pay_amount < amountRial
      || body.payment_link !== 'https://cubevps.ir/smspay/pay.php?authority=' + body.authority) throw new CubePayError('Creation not confirmed; preserve the order and reconcile');
    return body;
  }
  async verify(authority, expectedOrderId, requestedRial, savedPayRial) {
    if (typeof authority !== 'string' || !/^[a-f0-9]{50}$/.test(authority)) throw new TypeError('Invalid authority');
    if (!Number.isSafeInteger(requestedRial) || !Number.isSafeInteger(savedPayRial) || requestedRial < 1000 || savedPayRial < requestedRial) throw new TypeError('Load amounts from your stored order');
    const [code, body] = await this.#transport('verify-payment.php', { authority });
    if (code === 402 && body.status === 'pending' && body.success === false) return { state: 'pending' };
    if (code === 410 && body.success === false) return { state: 'unpaid' };
    if (code === 409 && body.status === 'verified') return { state: 'reconcile' };
    if (code !== 200 || body.success !== true || body.status !== 'verified' || body.order_id !== expectedOrderId
      || !Number.isSafeInteger(body.amount) || body.amount < requestedRial || body.amount > savedPayRial) throw new CubePayError('Verification not bound to stored order; reconcile');
    return { state: 'verified', amount_rial: body.amount };
  }
}
