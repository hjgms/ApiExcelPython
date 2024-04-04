from flask import Flask, jsonify, request
from main.tasks import TaskProgram
from main.database import Database


app = Flask(__name__)
queue = TaskProgram()
database = Database()


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
    items = request.json['items']
    filename = request.json['filename']
    usuario = request.json['usuario']
    empresa = request.json['empresa']

    # inserir a task na fila para controle de status
    codigo = database.insert_into_fila(
        usuario=usuario,
        empresa=empresa,
        arquivo=filename,
        status=0
    )
    print(codigo)
    task = {
        "id": codigo,
        "empresa": empresa,
        "usuario": usuario,
        "filename": filename,
        "items": items
    }

    queue.append_task(task)

    return jsonify({"id_task": codigo})


if __name__ == "__main__":
    # serve(app, host="localhost", port=4545)
    queue.run_tasks()
    app.run(host="localhost", port=4545)
