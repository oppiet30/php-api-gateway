import json
import os
import urllib.request

key = os.environ['GATEWAY_API_KEY']
base = os.environ.get('GATEWAY_URL', 'http://127.0.0.1:8000').rstrip('/')
url = base + '/api/v1/weather?latitude=41.8975&longitude=-84.0372'
request = urllib.request.Request(url, headers={'Authorization': f'Bearer {key}'})
with urllib.request.urlopen(request, timeout=20) as response:
    print(json.dumps(json.load(response), indent=2))
