# Funcionamento da api

### Argumentos suportados

    {
        "filename":"nome_do_arquivo.xlsx",
        "items": [
            # argumentos do excel
        ]
    }

## Exemplos


### Adicionar linhas

isso dentro do array "items", é possivel adicionar um objeto da linha

    {
        "row": [
            # itens da linha do excel
        ]
    }

### Adicionar item nas linhas

é obrigatorio adicionar o valor da celula, o tipo, e a posicao na coluna

    {
        "row": [
            {
                "value": "valor", # valor que será preenchido na celula
                "type": "string", # tipo da celula
                "pos": "A" # posicao na coluna
            }
        ]
    }

### Os tipos

    {
        "value": "Valoress",
        "type": "string", # texto sem formatação
        "pos": "A" 
    },
    {
        "value": "100", 
        "type": "integer", # inteiro em formatação
        "pos": "B" 
    },
    {
        "value": "90.10",
        "type": "price", # numero real com formatação moeda
        "pos": "C" 
    },
    {
        "value": "true",
        "type": "boolean", # verdadeiro ou falso
        "pos": "D" 
    },
    {
        "value": "2000-10-05",
        "type": "datetime", # data no formato BR ##/##/####
        "pos": "E" 
    },

### Exemplo geral

    {
        "filename":"mydocument.xlsx",
        "items":[
            {
                "row": [
                    {
                        "value": "Coluna1",
                        "type": "string",
                        "pos": "A"
                    },
                    {
                        "value": "Coluna2",
                        "type": "string",
                        "pos": "B"
                    },
                    {
                        "value": "Coluna3",
                        "type": "string",
                        "pos": "C"
                    },
                    {
                        "value": "Coluna4",
                        "type": "string",
                        "pos": "D"
                    }
                ]
            },
        ]
    }