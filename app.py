# Powered By c4e3bac3@foxmail.com Hello
from flask import Flask, request

app = Flask(__name__)

@app.route('/start', methods=['GET'])
def start():
    return '{"ContainerID": "a1234567890", "answer": "a1234567890", "message": "Hello World"}'

@app.route("/stop", methods=['POST'])
def stop():
    ContainerID = request.form['0']
    print(ContainerID)
    return "True"


if __name__ == "__main__":
    app.run(host="0.0.0.0", port=8081)
