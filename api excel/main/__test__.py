from requests import get, exceptions
from time import process_time, sleep
from os import getenv
from dotenv import load_dotenv

load_dotenv()

url_api = f"http://{getenv("SERVER_FTP")}:{getenv("PORT_FTP")}/"

def running_api():

    try:
        response = get(url_api)

        if response.json() == "Running... ":
            return 1
        else:
            print("Aplicação com problemas...")
            return 0
    except exceptions.ConnectionError:
        print("Aplicação fora do ar...")
        return 0


def process_test():
    items = []
    registros = 2000000

    print("=" * 30)
    print(f"Inicio da função de teste com {registros} registros simples.")

    if running_api() == 0:
        return

    for i in range(0, registros):
        items.append({"value": "Teste de Processo", "type": "string", "pos": f"A{i}"})

    filename = "newTestFromTest.xlsx"

    doc_json = {
        "usuario": 1,
        "empresa": 91,
        "filename": filename,
        "items": items
    }

    response = get( f"{url_api}excel/generate/", json=doc_json )

    print(f"Resultado do pedido de criação do excel: codigo -> {response.json()["id_task"]}\nTempo: {process_time():.2f} segundos")
    codigo = response.json()["id_task"]

    print("Geração do excel: Em processo")
    data_excel = {}
    while True:
        sleep(2)

        doc_json = {
            "usuario": 1,
            "empresa": 91,
            "id": codigo
        }

        response = get( f"{url_api}excel/status/", json=doc_json )

        if response.json()["status"] == "Finalizado":
            data_excel = response.json()

            path_file = data_excel["path_file"].split("/tmp/")
            if path_file[1] != filename:
                return print("erro, arquivo retornado com nome diferente do solicitado")

            print(f"Resultado da verificação de conclusão do excel: {response.json()["status"]}\nTempo: {process_time():.2f} segundos")
            break

    print("=" * 30)
    print(f"Data do arquivo: {data_excel["date_request"]}\nCaminho do arquivo: {data_excel["path_file"]}")
    print("=" * 30)


process_test()
