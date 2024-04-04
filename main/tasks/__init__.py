from queue import Queue, Empty
from threading import Event, Thread
from main.generate import ExcelGenerator
from main.database import Database


class TaskProgram:
    def __init__(self):
        self.task_queue = Queue()
        self.stop_event = Event()
        self.thread = Thread(target=self.process_task)
        self.generator = ExcelGenerator()
        self.database = Database()

    def run_tasks(self):
        self.thread.start()

    def process_task(self):
        while not self.stop_event.is_set():
            try:
                task = self.task_queue.get(timeout=1)
                # start task
                # print(task)
                items = task["items"]
                filename = task["filename"]
                codigo = task["id"]
                empresa = task["empresa"]
                usuario = task["usuario"]


                self.generator.set_filename(filename=filename)

                # generate excel
                try:
                    self.generator.write_document(doc=items)
                except Exception as err:
                    print(err)

                # finish task
                self.database.change_status(
                    codigo=codigo,
                    empresa=empresa,
                    usuario=usuario
                )

                self.task_queue.task_done()
            except Empty:
                pass

    def append_task(self, item):
        self.task_queue.put(item)

    def get_task_status(self, codigo, usuario, empresa):

        # teste de verificação da empresa
        item = self.database.get_item_fila(
            usuario=usuario,
            empresa=empresa,
            codigo=codigo
        )

        nome_arquivo, status, data = item

        response = {
            "path_file": "",
            "status": "",
            "date_request": data
        }

        print(status)
        if status == 0:
            response["status"] = "Em progresso"
        else:
            response["path_file"] = self.generator.get_file_path(name=nome_arquivo)
            response["status"] = "Finalizado"

        return response
