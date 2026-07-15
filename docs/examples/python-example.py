import requests

API_TOKEN = "YOUR_API_TOKEN"
BASE_URL = "https://cubevps.ir/smspay/api"

headers = {
    "Content-Type": "application/json",
    "Authorization": f"Bearer {API_TOKEN}",
}


def create_payment(amount, order_id, callback_url, description=None, customer_user_id=None):
    payload = {
        "amount": amount,
        "order_id": order_id,
        "callback_url": callback_url,
        "type": "card",
    }
    if description:
        payload["description"] = description
    if customer_user_id:
        payload["customer_user_id"] = customer_user_id

    response = requests.post(f"{BASE_URL}/create-payment.php", json=payload, headers=headers)
    return response.json()


def verify_payment(authority):
    response = requests.post(f"{BASE_URL}/verify-payment.php", json={"authority": authority}, headers=headers)
    return response.json()


if __name__ == "__main__":
    payment = create_payment(200000, "ORD123", "https://yourbot.example.com/callback", description="شارژ کیف پول")
    print(payment)

    if payment.get("success"):
        print("لینک پرداخت:", payment["payment_link"])
