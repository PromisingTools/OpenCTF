# Powered By c4e3bac3@foxmail.com Hello
from flask import Flask, request

app = Flask(__name__)

CONTAINER_API_TOKEN = "020c84a3d3841be34f4806dad78cff1dc7f34fe99ca11afd"

@app.route('/start', methods=['GET'])
def start():
    if request.headers.get('X-Auth-Token') != CONTAINER_API_TOKEN:
        return '{"error": "unauthorized"}', 401
    return '{"ContainerID": "a1234567890", "answer": "a1234567890", "message": "Hello World"}'

@app.route("/stop", methods=['POST'])
def stop():
    if request.headers.get('X-Auth-Token') != CONTAINER_API_TOKEN:
        return '{"error": "unauthorized"}', 401
    ContainerID = request.form['0']
    print(ContainerID)
    return "True"


if __name__ == "__main__":
    app.run(host="0.0.0.0", port=8081)
