# Funcionamento da api

### Argumentos suportados

    {
        "filename":"nome_do_arquivo.xlsx",
        "items": [
            # argumentos do excel
        ]
    }

## Exemplos

### Adicionar itens

é obrigatorio adicionar o valor da celula, o tipo, e a posicao na coluna

    {
        "value": "valor", # valor que será preenchido na celula
        "type": "string", # tipo da celula
        "pos": "A1" # posicao na coluna
    }

### Os tipos

    {
        "value": "Valoress",
        "type": "string", # texto sem formatação
        "pos": "A1" 
    },
    {
        "value": "100", 
        "type": "integer", # inteiro em formatação
        "pos": "B1" 
    },
    {
        "value": "90.10",
        "type": "price", # numero real com formatação moeda
        "pos": "C1" 
    },
    {
        "value": "true",
        "type": "boolean", # verdadeiro ou falso
        "pos": "D1" 
    },
    {
        "value": "2000-10-05",
        "type": "datetime", # data no formato BR ##/##/####
        "pos": "E1" 
    }

### Exemplo geral

    {
        "usuario": 1,
        "empresa": 9,
        "filename":"mydocument.xlsx",
        "items":[
            {
                "value": "Coluna1",
                "type": "string",
                "pos": "A1"
            },
            {
                "value": "Coluna2",
                "type": "string",
                "pos": "B1"
            },
            {
                "value": "Coluna3",
                "type": "string",
                "pos": "C1"
            },
            {
                "value": "Coluna4",
                "type": "string",
                "pos": "D1"
            }
        ]
    }

### Instalação das libs

    pip install pysqlite3
    pip install xlsxwriter
    pip install pillow
    pip install flask
    pip install requests