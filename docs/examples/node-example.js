const axios = require("axios");

const API_TOKEN = "YOUR_API_TOKEN";
const BASE_URL = "https://cubevps.ir/smspay/api";

const client = axios.create({
  baseURL: BASE_URL,
  headers: {
    "Content-Type": "application/json",
    Authorization: `Bearer ${API_TOKEN}`,
  },
});

async function createPayment({ amount, orderId, callbackUrl, description, customerUserId }) {
  const { data } = await client.post("/create-payment.php", {
    amount,
    order_id: orderId,
    callback_url: callbackUrl,
    type: "card",
    ...(description && { description }),
    ...(customerUserId && { customer_user_id: customerUserId }),
  });
  return data;
}

async function verifyPayment(authority) {
  const { data } = await client.post("/verify-payment.php", { authority });
  return data;
}

(async () => {
  try {
    const payment = await createPayment({
      amount: 200000,
      orderId: "ORD123",
      callbackUrl: "https://yourbot.example.com/callback",
      description: "شارژ کیف پول",
    });

    console.log(payment);

    if (payment.success) {
      console.log("لینک پرداخت:", payment.payment_link);
    }
  } catch (err) {
    console.error(err.response?.data ?? err.message);
  }
})();

module.exports = { createPayment, verifyPayment };
