"""CubePay regular card API. Amount arguments are integer RIALS; no implicit retries."""
import json
import re
import urllib.request
import urllib.error


class CubePayError(Exception):
    pass


class NoRedirect(urllib.request.HTTPRedirectHandler):
    def redirect_request(self, *args, **kwargs):
        return None


def toman_to_rial(value):
    if type(value) is not int or not 1 <= value <= 900719925474099:
        raise ValueError("Expected positive integer tomans")
    return value * 10


class CubePay:
    def __init__(self, token, transport=None):
        if not isinstance(token, str) or not token or re.search(r"\s", token):
            raise ValueError("Set CUBEPAY_TOKEN on the server")
        self._token = token
        self._transport = transport or self._http

    def _http(self, endpoint, data):
        req = urllib.request.Request("https://cubevps.ir/smspay/api/" + endpoint,
            data=json.dumps(data).encode(), headers={"Authorization": "Bearer " + self._token,
            "Content-Type": "application/json", "Accept": "application/json"}, method="POST")
        try:
            response = urllib.request.build_opener(NoRedirect).open(req, timeout=15)
        except urllib.error.HTTPError as e:
            response = e
        except (OSError, urllib.error.URLError):
            raise CubePayError("Unknown network outcome; reconcile before retrying") from None
        with response:
            body = response.read(65537)
            if len(body) > 65536:
                raise CubePayError("Oversized response; reconcile")
            try:
                parsed = json.loads(body)
            except (ValueError, UnicodeError):
                raise CubePayError("Invalid API response; reconcile") from None
            if not isinstance(parsed, dict):
                raise CubePayError("Invalid response object")
            return response.code, parsed

    def create(self, order_id, amount_rial, callback_url, customer_user_id=None, customer_username=None):
        if not isinstance(order_id, str) or not 1 <= len(order_id) <= 64 or order_id.strip() != order_id or order_id.startswith("topup-"):
            raise ValueError("Use a unique ordinary order ID, at most 64 characters")
        if type(amount_rial) is not int or not 1000 <= amount_rial <= 9007199254740991:
            raise ValueError("amount_rial must be a positive integer >= 1000")
        if not isinstance(callback_url, str) or not callback_url.startswith("https://") or len(callback_url) > 500:
            raise ValueError("Use your public HTTPS callback URL")
        data = {"order_id": order_id, "amount": amount_rial, "callback_url": callback_url}
        if customer_user_id is not None:
            data["customer_user_id"] = str(customer_user_id)
        if customer_username is not None:
            data["customer_username"] = str(customer_username).lstrip("@")
        code, result = self._transport("create-payment.php", data)
        if code != 200 or result.get("success") is not True:
            raise CubePayError("Creation not confirmed; preserve the order and reconcile")
        authority = result.get("authority", "")
        if not isinstance(authority, str) or not re.fullmatch(r"[a-f0-9]{50}", authority):
            raise CubePayError("Invalid authority")
        pay = result.get("pay_amount")
        if type(pay) is not int or not amount_rial <= pay <= 9007199254740991:
            raise CubePayError("Invalid payable amount")
        if result.get("payment_link") != "https://cubevps.ir/smspay/pay.php?authority=" + authority:
            raise CubePayError("Unexpected payment URL")
        return result

    def verify(self, authority, expected_order_id, requested_rial, saved_pay_rial):
        if not isinstance(authority, str) or not re.fullmatch(r"[a-f0-9]{50}", authority):
            raise ValueError("Invalid authority")
        if type(requested_rial) is not int or type(saved_pay_rial) is not int or not 1000 <= requested_rial <= saved_pay_rial:
            raise ValueError("Load expected amounts from your stored order")
        code, result = self._transport("verify-payment.php", {"authority": authority})
        if code == 402 and result.get("status") == "pending" and result.get("success") is False:
            return {"state": "pending"}
        if code == 410 and result.get("success") is False:
            return {"state": "unpaid"}
        if code == 409 and result.get("status") == "verified":
            return {"state": "reconcile"}  # No amount in this response; never credit from it.
        amount = result.get("amount")
        if (code != 200 or result.get("success") is not True or result.get("status") != "verified"
            or result.get("order_id") != expected_order_id or type(amount) is not int
            or not requested_rial <= amount <= saved_pay_rial):
            raise CubePayError("Verification not bound to the stored order; reconcile")
        return {"state": "verified", "amount_rial": amount}
