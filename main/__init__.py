from flask import Flask, jsonify, request
from generate import ExcelGenerator
from waitress import serve

app = Flask(__name__)


@app.route('/', methods=["GET"])
def hello():
    return jsonify("Running... ")


@app.route('/excel/generate/', methods=["GET"])
def generate_excel():
    # body
    items = request.json['items']
    filename = request.json['filename']
    generator = ExcelGenerator(filename=filename)

    # generate excel
    try:
        generator.write_document(doc=items)
    except Exception as err:
        return jsonify({"error_in_write_excel": err})

    path = generator.get_file_path()
    return jsonify({"file_path": path})


if __name__ == "__main__":
    serve(app, host="localhost", port=4545)
