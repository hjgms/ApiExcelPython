from sqlite3 import connect
from os import path


class Database:
    def __init__(self):
        self.path = path.dirname(__file__)

    def open(self):
        return connect(f'{self.path}/relatorios.db')

    def insert_into_fila(self, usuario="", empresa=0, arquivo="", status=0):
        db = self.open()

        codigo = self.get_last_item_fila()

        db.cursor().execute(
            "INSERT INTO fila (id, usuario, codigo_empresa, nome_arquivo, status, data) VALUES (?, ?, ?, ?, ?, DATETIME('now'))",
            (codigo, usuario, empresa, arquivo, status)
        )

        db.commit()
        db.close()
        return codigo

    def get_item_fila(self, usuario, empresa, codigo):
        db = self.open()

        response = db.cursor().execute(
            "SELECT nome_arquivo, status, data FROM fila WHERE usuario = ? AND codigo_empresa = ? AND id = ?",
            (usuario, empresa, codigo)
        ).fetchone()

        db.close()
        return response

    def get_last_item_fila(self):
        db = self.open()

        response = db.cursor().execute("SELECT (MAX(id) + 1) AS codigo FROM fila").fetchone()

        db.close()
        return response[0]

    def change_status(self, usuario=0, empresa=0, codigo=0):
        db = self.open()

        db.cursor().execute(
            "UPDATE fila SET status = 1 WHERE usuario = ? AND codigo_empresa = ? AND id = ?",
            (usuario, empresa, codigo)
        )

        db.commit()
        db.close()
