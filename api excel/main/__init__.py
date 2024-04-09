from flask import Flask, jsonify, request
from tasks import TaskProgram
from os import getenv
from dotenv import load_dotenv

load_dotenv()

app = Flask(__name__)
queue = TaskProgram()


@app.route('/', methods=["GET"])
def hello():
    return jsonify("Running... ")


@app.route('/excel/status/', methods=["GET"])
def get_excel_queue():

    response = queue.get_task_status(
        usuario=request.json['usuario'],
        empresa=request.json['empresa'],
        codigo=request.json['id']
    )

    return jsonify(response)


@app.route('/excel/generate/', methods=["GET"])
def add_excel_queue():

    # body
    # items = request.json['items']
    # filename = request.json['filename']
    # usuario = request.json['usuario']
    # empresa = request.json['empresa']

    # inserir a task na fila para controle de status
    codigo = queue.append_task(request.json)

    return jsonify({"id_task": codigo})


if __name__ == "__main__":
    
    if queue.run_tasks():
        app.run(host=getenv("SERVER_FTP"), port=getenv("PORT_FTP"))
