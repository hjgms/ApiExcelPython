from sqlite3 import connect
from os import path


class Database:
    def __init__(self):
        self.path = path.dirname(__file__)

    def open(self):
        return connect(f'{self.path}/relatorios.db')

    def insert_into_fila(self, usuario=0, empresa=0, arquivo="", status=0):
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

    def delete_item_fila(self, usuario=0, empresa=0, codigo=0):
        db = self.open()

        db.cursor().execute(
            "DELETE FROM fila WHERE usuario = ? AND codigo_empresa = ? AND id = ?",
            (usuario, empresa, codigo)
        )

        db.commit()
        db.close()


class DatabaseTest:
    def __init__(self):
        self.db = Database()

        self.id_exemple_test = 0
        
    def run_test(self):

        try:
            if self.insert_test():
                print("passed insert test")
            else:
                return "not passed insert test"
        except:
            return "error insert test."
        
        try:
            if self.select_test():
                print("passed select test")
            else:
                return "not passed select test"
        except:
            return "error select test."
        
        try:
            if self.update_test():
                print("passed update test")
            else:
                return "not passed update test"
        except:
            return "error update test."
        
        try:
            if self.delete_test():
                print("passed delete test")
            else:
                return "not passed delete test"
        except:
            return "error delete test."
        
        return True


    def insert_test(self):

        filename = "archive_test"
        self.id_exemple_test = self.db.insert_into_fila(
            usuario=1,
            empresa=1,
            arquivo=filename,
            status=0
        )

        if self.id_exemple_test > 0:
            response = self.db.get_item_fila(
                usuario=1,
                empresa=1,
                codigo=self.id_exemple_test
            )

            if response[0] == filename:
                return True
            
        return False


    def select_test(self):
        
        response = self.db.get_item_fila(
            usuario=1,
            empresa=1,
            codigo=self.id_exemple_test
        )

        if response:
            return True

        return False


    def update_test(self):

        self.db.change_status(
            usuario=1,
            empresa=1,
            codigo=self.id_exemple_test
        )

        response = self.db.get_item_fila(
            usuario=1,
            empresa=1,
            codigo=self.id_exemple_test
        )

        if response[1] == 1:
            return True
        
        return False


    def delete_test(self):
        
        self.db.delete_item_fila(
            usuario=1,
            empresa=1,
            codigo=self.id_exemple_test
        )

        response = self.db.get_item_fila(
            usuario=1,
            empresa=1,
            codigo=self.id_exemple_test
        )

        if response:
            return False

        return True
