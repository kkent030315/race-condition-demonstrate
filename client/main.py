import threading
import requests
import urllib

BANK_TOKENS = {
    'a': 'redacted',
    'b': 'redacted'
}
NUMBER_TO_EXECUTE = 500
API_ENDPOINT = 'http://localhost/api/'


def perform_transfer(token: str, recipient: str, amount: int) -> bool:
    headers = {
        'X-ABCBANK-TOKEN': token,
        'Content-Type': 'application/x-www-form-urlencoded'
    }

    form_data = urllib.parse.urlencode({
        'a': 'transfer',
        'recipient': recipient,
        'amount': amount
    })

    response = requests.post(
        f'{API_ENDPOINT}',
        headers=headers, data=form_data
    )

    status_code = response.status_code

    if not status_code == 200:
        raise Exception('not valid request')

    response_json = response.json()
    response_status_code = response_json['status']

    if not response_status_code == 1:
        return False

    return True


def thread_function_1() -> None:
    for _ in range(NUMBER_TO_EXECUTE):
        perform_transfer(BANK_TOKENS['a'], 'b', 5)


def thread_function_2() -> None:
    for _ in range(NUMBER_TO_EXECUTE):
        perform_transfer(BANK_TOKENS['b'], 'a', 5)


def main() -> None:
    t1 = threading.Thread(target=thread_function_1)
    t2 = threading.Thread(target=thread_function_2)

    t1.start()
    t2.start()


if __name__ == '__main__':
    main()
