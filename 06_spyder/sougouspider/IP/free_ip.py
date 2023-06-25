# coding=utf-8
import requests

proxypool_url = 'http://127.0.0.1:5555/random'

def get_random_proxy():
    response = requests.get(proxypool_url)
    try:
        if response.status_code == 200:
            return response.text.strip()
    except ConnectionError:
        return None

if __name__ == '__main__':
    print(get_random_proxy())


