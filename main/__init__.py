from flask import Flask, jsonify, request, send_file
from generate import ExcelGenerator

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
    generator.write_document(doc=items)
    path = generator.get_file_path()
    # return path
    return send_file(path)


# @app.route('/excel/last/', methods=["GET"])
# def generate_excel():
#     # body
#     path = ExcelGenerator.get_imgs_path() +
#     # path = ""
#     return path


if __name__ == "__main__":
    app.run()
