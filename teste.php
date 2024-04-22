		$api_excel = new SendRequestApiExcel();

		

		// "value": "Teste de Processo", "type": "string", "pos": "A{i}"

		$tituloColunasExcel = array(
			'CÓDIGO INTERNO',
			'CÓDIGO FÁBRICA',
			'CODIGOBARRAS',
			'DESCRIÇÃO COMPLETA',
			'DESCRIÇÃO COMERCIAL',
			'GRUPO',
			'SUBGRUPO',
			'MARCA',
			'LINHA',
			'MODELO',
			'VOLTAGEM',
			'COR',
			'NCM',
			'UF ORIGEM',
			'PREÇO VENDA',
			'PREÇO DE FÁBRICA',
			'DESCONTO %',
			'IPI %',
			'ALIQICMSORIGEM',
			'ALIQICMSINTERNA',
			'IVA',
			'FRETE R$',
			'FRETE %',
			'UNIDADE',
			'QTDE EMBALAGEM DE VENDA',
			'CST',
			'ALIQUOTA COFINS CST',
			'ALIQUOTA IPI CST',
			'ALIQUOTA PIS CST',
			'CSOSN',
			'CFOP DENTRO',
			'CFOP FORA',
			'PESOLIQ',
			'PESOBRUTO',
			'QTDE EMBALAGEM DE COMPRA',
			'VALOR PI',
			'ALIQUOTA COFINS',
			'ALIQUOTA PIS',
			'PERCENTUAL ST',
			'UNID FABRIL',
			'OBSERVAÇÃO',
			'DIFERENÇA ICMS',
			'REDUÇÃO BASE ICMS',
			'REDUÇÃO BASE ST',
			'RETENÇÃO PIS',
			'RETENÇÃO COFINS',
			'RETENÇÃO CSLL',
			'RETENÇÃO IRRF',
			'RETENÇÃO PREV. SOCIAL',
			'LOCALIZAÇÃO',
			'ENQUADRAMENTO IPI',
			'ALIQUOTA PIS ORIGEM',
			'ALIQUOTA COFINS ORIGEM',
			'IMAGEM'
		);

		$linha1 = array();
		foreach($tituloColunasExcel as $key => $item) {
			$linha1 += ["value" => $item, "type" => "string", "pos" => $api_excel->numberToChar($key)."1"];
		}

		$aleatorio = rand(0, 999);
		$fileName = "ProdutosImportacao_{$token->emp}_{$aleatorio}.xlsx";
		$data_api = array(
			'empresa' => $args['codempresa'],
			'usuario' => 1,
			'filename' => $fileName,
			'items' => []
		);

		$api_excel->generateExcel($data_api);

class SendRequestApiExcel {
  
		private $api_url = 'http://localhost:4545';
		private $route_generate = '/excel/generate/';
		private $route_status = '/excel/status/';
	  
		function getRequest($route, $data) {
	  
		  $ch = curl_init($this->api_url.$route);
	  
		  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
		  curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		  curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
	  
		  $response = curl_exec($ch);
		  curl_close($ch);
	  
		  return json_decode($response);
		}
	  
		function generateExcel($data) {
		  $response = new stdClass();
	  
		  try {
			$request = $this->getRequest($this->route_generate, $data);
	  
			if(!(intval($request->id_task) > 0)) {
			  return "number invalid";
			}
	  
			$response->retorno = "OK";
			$response->detalhes = intval($request->id_task);
			return $response;
		  } catch(PDOException $err) {
	  
			$response->retorno = "ERROR";
			$response->detalhes = $err;
			return $response;
		  }
		}
	  
		function statusExcel($data) {
		  $response = new stdClass();
	  
		  try {
			$request = $this->getRequest($this->route_status, $data);
	  
			if(intval($request->status) == 1) {
			  $response->retorno = "OK";
			  $response->detalhes = $request;
			}
	  
			return $response;
		  } catch(PDOException $err) {
			
			$response->retorno = "ERROR";
			$response->detalhes = $err;
			return $response;
		  }
		}

		function numberToChar($number) {
			$a = range('A', 'Z');

			if($number <= 26) {
				return $a[$i];
			}

			$repeat = floor(($number - 1) / 26);

			$i = ($number - 1) % 26;

			return str_repeat('A', $repeat) . $a[$i];
		}
	}

?>
