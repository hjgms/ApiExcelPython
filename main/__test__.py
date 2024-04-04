from requests import get
from time import process_time


def process_test():
    items = []
    registros = 2000000

    print("=" * 30)
    print(f"Inicio da função de teste com {registros} registros simples.")

    for i in range(0, registros):
        items.append({"value": "Teste de Processo", "type": "string", "pos": f"A{i}"})

    doc_json = {
        "usuario": 1,
        "empresa": 91,
        "filename": "newTestFromTest.xlsx",
        "items": items
    }
    response = get(
        "http://localhost:4545/excel/generate/",
        json=doc_json
    )

    print(f"Resultado do pedido de criação do excel: codigo -> {response.json()["id_task"]}\nTempo: {process_time():.2f} segundos")
    codigo = response.json()["id_task"]

    print("Geração do excel: Em processo")
    data_excel = {}
    while True:
        doc_json = {
            "usuario": 1,
            "empresa": 91,
            "id": codigo
        }
        response = get(
            "http://localhost:4545/excel/status/",
            json=doc_json
        )

        if response.json()["status"] == "Finalizado":
            data_excel = response.json()
            print(f"Resultado da verificação de conclusão do excel: {response.json()["status"]}\nTempo: {process_time():.2f} segundos")
            break

    print("=" * 30)
    print(f"Data do arquivo: {data_excel["date_request"]}\nCaminho do arquivo: {data_excel["path_file"]}")
    print("=" * 30)


process_test()
