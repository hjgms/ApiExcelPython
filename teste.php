<?php
	require_once ("PDFF.php");
	require_once ("funcoes.php");

    use Slim\Http\Request;
    use Slim\Http\Response;
    use Firebase\JWT\JWT;

    header('Access-Control-Allow-Origin:*');
    header("Access-Control-Allow-Methods:GET,PUT,POST,DELETE");
    header("Access-Control-Allow-Headers:Origin, X-Requested-With,Content-Type,Accept,Authorization");

    $app->get('/relfrmcadproduto/{codempresa}', function ($request, $response, $args) {
		$token = $request->getAttribute("token");
        $params = $this->request->getQueryParams();
        $resultado = new stdClass();

		ini_set('memory_limit','1024M');

		$colunas = [
			'm.DESCRICAO as MARCA',
	        'l.DESCRICAO as LINHA',
	        'g.DESCRICAO as GRUPO',
	        's.DESCRICAO as SUBGRUPO',
	        'u.SIGLA     as SIGLAUN',      
	        'ROUND(COALESCE(estp.DISPONIVEL, 0),2) AS ESTOQUE_DISPONIVEL', 
	        'COALESCE(pp.VALORVENDA, 0) as VALORDEVENDA', 
			'pp.CODIGO as CODIGOTABELAPRECO', 
			'origem.ALIQUOTA AS ORIGEMALIQUOTA', 
			'icms.ALIQUOTA AS ICMSALIQUOTA',
	        'p.*', 
			"'PRODUTO' AS TIPO", 
			"p.COD_CST_COFINS as ALIQUOTACOFINSCST", 
			"p.COD_CST_IPI as ALIQUOTAIPICST", 
			"p.COD_CST_PIS as ALIQUOTAPISCST", 
			"p.ALIQ_COFINS as ALIQUOTACOFINS", 
			"p.ALIQ_PIS as ALIQUOTAPIS", 
			"p.COD_CST_TABELA_A", 
			"p.COD_CST_TABELA_B",
	        "pp2.VALOR_PROMOCAO AS VALOR_PROMOCAO",
	        "pp2.INICIO_PROMOCAO AS INICIO_PROMOCAO",
	        "pp2.FIM_PROMOCAO AS FIM_PROMOCAO",
	        "pp2.LANC_PROMOCAO AS LANC_PROMOCAO", 
			"emp.PRECOUNICOMATRIZFILIAIS AS PRECO_UNICO", 
			"empm.PRECOUNICOMATRIZFILIAIS AS PRECO_UNICO_MATRIZ",
		];

		if ($params["tipo"] == "EXC_TRIBUTACAO") {
			$colunas = [
				"p.CODIGO", 
				"p.REFERENCIA", 
				"p.DESCRICAO", 
				"p.NCM", 
				"p.UFORIGEM", 
				"p.IPICUSTO", 
				"icms.ALIQUOTA AS ICMSALIQUOTA", 
				"origem.ALIQUOTA AS ORIGEMALIQUOTA", 
				"p.IVA_MVA", 
				"p.IVAAJUSTADO", 
				"p.COD_CSOSN", 
				"p.COD_CFOPDENTRO", 
				"p.COD_CFOPFORA", 
				"p.PERC_ST", 
				"p.COD_CST_TABELA_A", 
				"p.COD_CST_TABELA_B", 
				"emp.PRECOUNICOMATRIZFILIAIS AS PRECO_UNICO", 
				"empm.PRECOUNICOMATRIZFILIAIS AS PRECO_UNICO_MATRIZ", 
				"pp2.LANC_PROMOCAO AS LANC_PROMOCAO", 
				'pp.CODIGO as CODIGOTABELAPRECO', 
				"pp2.VALOR_PROMOCAO AS VALOR_PROMOCAO", 
				"pp2.INICIO_PROMOCAO AS INICIO_PROMOCAO", 
				"pp2.FIM_PROMOCAO AS FIM_PROMOCAO", 
				"p.DIFERENCAICMSCUSTO", 
				"p.DESCONTOCUSTO", 
				"COALESCE(pp.VALORVENDA, 0) as VALORDEVENDA", 
			];
		}

		if ($params['tipopreco'] == 0) {
			$colunas[] = 'p.PRECOMEDIOCOMPRA as VALORCOMPRA';
		} else if($params['tipopreco'] == 1) {
			$colunas[] = 'p.PRECOFABRICA as VALORCOMPRA';
		} else if($params['tipopreco'] == 2) {
			$colunas[] = 'p.ULTIMO_PRECO as VALORCOMPRA';
		}

        $classFrmcadproduto = new frmcadproduto();
        $res = $classFrmcadproduto->getProdutos($this->db_rel, $params, $args['codempresa'], $colunas);
        if($res->retorno != "OK")
        {
			$resultado->retorno = "ERRO";
            $resultado->detalhes = $res->detalhes;
            return $this->response->withJson($resultado);
        }
        else if(sizeof($res->produtos) == 0)
        {
        	$resultado->retorno = "ERRO";
            $resultado->detalhes = "Sem informação para a solicitação";
            return $this->response->withJson($resultado);
        }

		error_log(sizeof($res->produtos));

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

		if(sizeof($res->produtos) > 35000 || $res->qtdeimagens > 0) {
			
		}

        // if($params["tipo"] != 'EXC' && $params["tipo"] != "EXC_IMPORTACAO" && $params["tipo"] != "EXC_TRIBUTACAO")
        // {
        // 	global $titulo;
		// 	global $subtitulo;
		// 	global $titulocol;
		// 	global $tamanhocol;
		// 	global $dadosempresa;
		// 	global $aligncol;

		// 	$titulocol = array(array("Marca", "Codigo", "Referencia", "Descricao", "Modelo", "Estoque", "Valor Venda", "Cor", "Voltagem", "Localizacao"));

		// 	$tamanhocol= array(array(30.0, 17.0, 32.0, 72.0, 24.0, 17.0, 20.0, 21.0, 15.0, 30.0));
		// 	$aligncol  = array(array("C", "C", "C", "C", "C", "C", "C", "C", "C", "C"));
		// 	$tamanhototal = 278;

		// 	$resultado = new stdClass();

		// 	$subtitulo = $params["filtros"];

		// 	$titulo="Listagem produtos";

		// 	$sql="Select e.*, c.NOME as CIDADE From empresa e left outer join cidade c on(e.COD_CIDADE = c.CODIGO and e.CODIGO = c.COD_EMPRESA) where e.CODIGO = :codempresa";
        //     $sth = $this->db_rel->prepare($sql);
        //     try {
        //         $sth->bindValue("codempresa", $args['codempresa']);
        //         $sth->execute();
        //     } catch (PDOException $e) {
        //         $resultado->retorno = "ERRO";
        //         $resultado->detalhes = $e->getMessage();
        //         return $resultado;
        //     }

        //     if($dadosempresa = $sth->fetch(PDO::FETCH_OBJ)) {
        //     	if($dadosempresa->CODIGO == null) {
        //     		$resultado->retorno="N/E";
		// 			$resultado->detalhes="Dados da empresa não cadastrado!";
		// 			return $resultado;
        //     	}
        //     }
			
		// 	$pdf = new PDFF("L","mm","A4");
		// 	$pdf->SetMargins(10,10,10);
		// 	$pdf->AliasNbPages();
		// 	$pdf->AddPage();
		// 	$pdf->SetFont('Arial', '', 8); 	 		
		// 	$pdf->SetDrawColor(139, 105, 20);
		// 	$pdf->SetFillColor(242, 234, 221);

		// 	$pdf->SetFont('Arial', '', 8); 

        // 	foreach ($res->produtos as $prod)
	    // 	{
	    // 		$prod->VOLTAGEM = $classFrmcadproduto->retornaVoltagem($prod->VOLTAGEM);

	    // 		$pdf->Cell(30, 5, $prod->MARCA, 'L', 0, "L");
		// 		$pdf->Cell(17, 5, $prod->CODIGO, 'L', 0, "L");
		// 		$pdf->Cell(32, 5, $prod->REFERENCIA, 'L', 0, "L");
		// 		$pdf->Cell(72, 5, substr($prod->DESCRICAO, 0, 44), 'L', 0, "L");
		// 		$pdf->Cell(24, 5, substr($prod->MODELO, 0, 13), 'L', 0, "L");
		// 		$pdf->Cell(17, 5, number_format($prod->ESTOQUE_DISPONIVEL, 2, ',', '.'), 'L', 0, "R");
		// 		$pdf->Cell(20, 5, number_format($prod->VALORVENDA, 2, ',', '.'), 'L', 0, "R");
		// 		$pdf->Cell(21, 5, $prod->COR, 'L', 0, "L");
		// 		$pdf->Cell(15, 5, $prod->VOLTAGEM, 'L', 0, "L");
		// 		$pdf->Cell(30, 5, $prod->LOCALIZACAO, 'LR', 1, "L");
	    // 	}

	    // 	$pdf->Cell(0, 5, '', 'T', 1, "L");


	    //     /*if(!$this->ftp['is_ftp']){
	    //         $dir = __DIR__."/..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."vue.js".DIRECTORY_SEPARATOR;
	    //     } else {
	    //         $dir = __DIR__."/..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR;
	    //     }*/

	    //     $dir = __DIR__.$this->caminho_diretorio;

	    //     if(!is_dir($dir."arquivos"))
	    //         mkdir($dir."arquivos", 0777, true);
	                
	    //     if(!is_dir($dir."arquivos".DIRECTORY_SEPARATOR.'tmp'))
	    //         mkdir($dir."arquivos".DIRECTORY_SEPARATOR.'tmp', 0777, true);

	    //     $aleatorio = rand(0, 999);
        //     $filename = "listagemprodutos_".$args['codempresa']."_".$aleatorio.".pdf";
        //     $arquivo = __DIR__."/tmp/".$filename;
        //     $pdf->Output($arquivo,'F');

	    //     copy($arquivo , $dir."arquivos".DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.$filename);
	        
        //     $resultado->arquivo=$arquivo;
        //     $resultado->filename = $filename;

        //     $http_s = "http://";
	    //     if(strpos($_SERVER["HTTP_HOST"], "localhost") === false)
	    //         $http_s = "https://";

	    //     $resultado->caminhoVue=$http_s.$this->get("host").DIRECTORY_SEPARATOR."arquivos".DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.$resultado->filename;
	    // }
	    // else
	    // {	    
	    // 	$resultado->arrexcel = array();
	    	
	    // 	if($params["tipo"] == "EXC_IMPORTACAO")
	    // 	{

		// 		$writer = new XLSXWriter();
		// 		$styles = [
		// 			"font-size" => 12,
		// 			"font" => "Calibri"
		// 		];

		// 		$colunasExcImportacao = array(
		// 			'CÓDIGO INTERNO' => 'integer',
		// 			'CÓDIGO FÁBRICA' => 'string',
		// 			'CODIGOBARRAS' => 'string',
		// 			'DESCRIÇÃO COMPLETA' => 'string',
		// 			'DESCRIÇÃO COMERCIAL' => 'string',
		// 			'GRUPO' => 'string',
		// 			'SUBGRUPO' => 'string',
		// 			'MARCA' => 'string',
		// 			'LINHA' => 'string',
		// 			'MODELO' => 'string',
		// 			'VOLTAGEM' => 'string',
		// 			'COR' => 'string',
		// 			'NCM' => 'string',
		// 			'UF ORIGEM' => 'string',
		// 			'PREÇO VENDA' => 'price',
		// 			'PREÇO DE FÁBRICA' => 'price',
		// 			'DESCONTO %' => 'price',
		// 			'IPI %' => 'price',
		// 			'ALIQICMSORIGEM' => 'price',
		// 			'ALIQICMSINTERNA' => 'price',
		// 			'IVA' => 'price',
		// 			'FRETE R$' => 'price',
		// 			'FRETE %' => 'price',
		// 			'UNIDADE' => 'string',
		// 			'QTDE EMBALAGEM DE VENDA' => 'price',
		// 			'CST' => 'string',
		// 			'ALIQUOTA COFINS CST' => 'string',
		// 			'ALIQUOTA IPI CST' => 'string',
		// 			'ALIQUOTA PIS CST' => 'string',
		// 			'CSOSN' => 'string',
		// 			'CFOP DENTRO' => 'string',
		// 			'CFOP FORA' => 'string',
		// 			'PESOLIQ' => 'price',
		// 			'PESOBRUTO' => 'price',
		// 			'QTDE EMBALAGEM DE COMPRA' => 'price',
		// 			'VALOR PI' => 'price',
		// 			'ALIQUOTA COFINS' => 'price',
		// 			'ALIQUOTA PIS' => 'price',
		// 			'PERCENTUAL ST' => 'price',
		// 			'UNID FABRIL' => 'string',
		// 			'OBSERVAÇÃO' => 'string',
		// 			'DIFERENÇA ICMS' => 'price',
		// 			'REDUÇÃO BASE ICMS' => 'price',
		// 			'REDUÇÃO BASE ST' => 'price',
		// 			'RETENÇÃO PIS' => 'price',
		// 			'RETENÇÃO COFINS' => 'price',
		// 			'RETENÇÃO CSLL' => 'price',
		// 			'RETENÇÃO IRRF' => 'price',
		// 			'RETENÇÃO PREV. SOCIAL' => 'price',
		// 			'LOCALIZAÇÃO' => 'string',
		// 			'ENQUADRAMENTO IPI' => 'string',
		// 			'ALIQUOTA PIS ORIGEM' => 'price',
		// 			'ALIQUOTA COFINS ORIGEM' => 'price',
		// 			'IMAGEM' => 'string'
		// 		);
				
		// 		$aleatorio = rand(0, 999);
		// 		$fileName = "ProdutosImportacao_{$token->emp}_{$aleatorio}.xlsx";

		// 		$dir = __DIR__.$this->caminho_diretorio;

		// 		if(!is_dir($dir."arquivos"))
		// 			mkdir($dir."arquivos", 0777, true);
						
		// 		if(!is_dir($dir."arquivos".DIRECTORY_SEPARATOR.'tmp'))
		// 			mkdir($dir."arquivos".DIRECTORY_SEPARATOR.'tmp', 0777, true);

		// 		$dir .= "arquivos".DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR;

		// 		//header
		// 		$writer->writeSheetHeader('Sheet1', $colunasExcImportacao, $styles);

	    // 		foreach ($res->produtos as $prod)
		//     	{
		// 			$freteR = 0;
		// 			$freteP = 0;
		// 			$sth = $this->db_rel->prepare("SELECT dfp.VALOR, d.TIPO, d.DESCRICAO FROM despesafixaproduto dfp LEFT JOIN despesa d ON d.COD_EMPRESA = dfp.COD_EMPRESA AND d.CODIGO = dfp.COD_DESPESA WHERE dfp.COD_PRODUTO = :codproduto AND dfp.COD_EMPRESA = :codempresa");
		// 			try {
		// 				$sth->bindValue("codempresa", $args['codempresa']);
		// 				$sth->bindValue("codproduto", $prod->CODIGO);
		// 				$sth->execute();
		// 			} catch (PDOException $e) {
		// 				$resultado->retorno = "ERRO";
		// 				$resultado->detalhes = $e->getMessage();
		// 				return $resultado;
		// 			}

		// 			while($dados = $sth->fetch(PDO::FETCH_OBJ)){
		// 				if(strpos(strtoupper($dados->DESCRICAO), "FRETE") > -1){
		// 					if($dados->TIPO == "V"){
		// 						$freteR = $dados->VALOR;
		// 					}else{
		// 						$freteP = $dados->VALOR;
		// 					}
		// 				}
		// 			}

		// 			$writer->writeSheetRow('Sheet1', array(
		// 				$prod->CODIGO, 
		// 				$prod->REFERENCIA, 
		// 				$prod->CODIGOBARRAS, 
		// 				$prod->DESCRICAO, 
		// 				$prod->DESCRICAOCOMERCIAL,
		// 				$prod->GRUPO,
		// 				$prod->SUBGRUPO,
		// 				$prod->MARCA,
		// 				$prod->LINHA,
		// 				$prod->MODELO,
		// 				$classFrmcadproduto->retornaVoltagem($prod->VOLTAGEM_ID),
		// 				substr($prod->COR, 0, 50),
		// 				$prod->NCM,
		// 				$prod->UFORIGEM,
		// 				$prod->VALORDEVENDA,
		// 				$prod->VALORCOMPRA,
		// 				$prod->DESCONTOCUSTO,
		// 				$prod->IPICUSTO,
		// 				$prod->ORIGEMALIQUOTA,
		// 				$prod->ICMSALIQUOTA,
		// 				$prod->IVA_MVA,
		// 				$freteR,
		// 				$freteP,
		// 				$prod->SIGLAUN,
		// 				$prod->EMBALAGEMVENDA,
		// 				$prod->CST,
		// 				$prod->ALIQUOTACOFINSCST,
		// 				$prod->ALIQUOTAIPICST,
		// 				$prod->ALIQUOTAPISCST,
		// 				$prod->COD_CSOSN,
		// 				$prod->COD_CFOPDENTRO,
		// 				$prod->COD_CFOPFORA,
		// 				$prod->PESOLIQ,
		// 				$prod->PESOBRUTO,
		// 				$prod->EMBALAGEM_COMPRA,
		// 				$prod->VALOR_PI,
		// 				$prod->ALIQ_COFINS,
		// 				$prod->ALIQ_PIS,
		// 				$prod->PERC_ST,
		// 				$prod->UNID_FABRIL,
		// 				$prod->OBSERVACAO,
		// 				$prod->PERC_DIF_ICMS,
		// 				$prod->REDUCAOBASECALCICMS,
		// 				$prod->REDUCAOBASECALCST,
		// 				$prod->RETENCAO_PIS,
		// 				$prod->RETENCAO_COFINS,
		// 				$prod->RETENCAO_CSLL,
		// 				$prod->RETENCAO_IRRF,
		// 				$prod->RETENCAO_PREV_SOCIAL,
		// 				$prod->LOCALIZACAO,
		// 				$prod->ENQUADRAMENTO_IPI,
		// 				$prod->ALIQ_PIS_ORIGEM,
		// 				$prod->ALIQ_COFINS_ORIGEM
		// 				// $prod->IMAGEM
		// 			), $styles);
		// 		}
				
		// 		$writer->writeToFile($dir.$fileName);

		// 		$http_s = "http://";
		// 		if (strpos($_SERVER["HTTP_HOST"], "localhost") === false) {
		// 			$http_s = "https://";
		// 		}

		// 		unset($writer);
		// 		$resultado->caminhoVue = $http_s.$this->get("host").DIRECTORY_SEPARATOR."arquivos".DIRECTORY_SEPARATOR.'tmp'.DIRECTORY_SEPARATOR.$fileName;
	    // 	}
		// 	else if($params["tipo"] == "EXC_TRIBUTACAO")
		// 	{
		// 		$sql="Select e.*, c.NOME as CIDADE From empresa e left outer join cidade c on(e.COD_CIDADE = c.CODIGO and e.CODIGO = c.COD_EMPRESA) where e.CODIGO = :codempresa";
		// 		$sth = $this->db_rel->prepare($sql);
		// 		try {
		// 			$sth->bindValue("codempresa", $args['codempresa']);
		// 			$sth->execute();
		// 		} catch (PDOException $e) {
		// 			$resultado->retorno = "ERRO";
		// 			$resultado->detalhes = $e->getMessage();
		// 			return $resultado;
		// 		}

		// 		if($dadosempresa = $sth->fetch(PDO::FETCH_OBJ)) {
		// 			if($dadosempresa->CODIGO == null) {
		// 				$resultado->retorno="N/E";
		// 				$resultado->detalhes="Dados da empresa não cadastrado!";
		// 				return $resultado;
		// 			}
		// 		}
				
		// 		foreach ($res->produtos as $prod)
		//     	{
		//     		$objAux = new stdClass();
	    //         	$objAux = json_decode(json_encode($objAux), true);	 
		//     		$objAux['CODIGO'] = $prod->CODIGO; 
	    //             $objAux['REFERENCIA'] = $prod->REFERENCIA; 
	    //             $objAux['DESCRICAO'] = $prod->DESCRICAO; 
	    //             $objAux['NCM'] = $prod->NCM;
	    //             $objAux['UF ORIGEM'] = $prod->UFORIGEM;
		// 			$objAux['IPI'] = number_format($prod->IPICUSTO, 2, ',', '');
	    //             $objAux['ICMS INTERNA'] = number_format($prod->ICMSALIQUOTA, 2, ',', '');
		// 			$objAux['ICMS ORIGEM'] = number_format($prod->ORIGEMALIQUOTA, 2, ',', '');
	    //             $objAux['IVA / MVA'] = number_format($prod->IVA_MVA, 2, ',', '');
		// 			$objAux['IVA AJUSTADO'] = number_format($prod->IVAAJUSTADO, 2, ',', '');
	    //             $objAux[$dadosempresa->TIPOEMPRESA == 1 ? 'CSOSN' : 'CST'] = $dadosempresa->TIPOEMPRESA == 1 ? $prod->COD_CSOSN : $prod->CST;
	    //             $objAux['CFOP OP INTERNO'] = $prod->COD_CFOPDENTRO;
	    //             $objAux['CFOP OP INTERESTADUAL'] = $prod->COD_CFOPFORA;
	    //             $objAux['PERC_ST'] = number_format($prod->PERC_ST, 2, ',', '');

	    //             $resultado->arrexcel[] = $objAux;
	    //         }
		// 	}
	    // 	else
	    // 	{
	    //     	$valor_venda = "Valor venda";
	    //     	$filtros = '';
	    //     	foreach($params['filtros'] as $filtro)
	    //     	{
		// 			$filtros .= $filtro.', ';
	    //     	}

	    //     	if($filtros != '')
	    //     	{
	    //     		$objAux = new stdClass();
	    //         	$objAux = json_decode(json_encode($objAux), true);	    		

		//     		$objAux['Marca'] = 'FILTROS '.rtrim($filtros, ', '); 
	    //             $objAux['Codigo'] = ''; 
	    //             $objAux['Referencia'] = ''; 
	    //             $objAux['Descricao'] = ''; 
	    //             $objAux['Modelo'] = '';
	    //             $objAux['Estoque'] = '';
	    //             $objAux[$valor_venda] = '';
	    //             $objAux['Cor'] = '';
	    //             $objAux['Voltagem'] = '';
	    //             $objAux['Localizacao'] = '';
		// 			$objAux['Enquadramento IPI'] = '';

	    //             $resultado->arrexcel[] = $objAux;
	    //     	}


	    //     	$objAux = new stdClass();	
	    //         $objAux = json_decode(json_encode($objAux), true);	    		

	    // 		$objAux['Marca'] = 'Marca'; 
	    //         $objAux['Codigo'] = 'Codigo'; 
	    //         $objAux['Referencia'] = 'Referencia'; 
	    //         $objAux['Descricao'] = 'Descricao'; 
	    //         $objAux['Modelo'] = 'Modelo';
	    //         $objAux['Estoque'] = 'Estoque';
	    //         $objAux[$valor_venda] = 'Valor venda';
	    //         $objAux['Cor'] = 'Cor';
	    //         $objAux['Voltagem'] = 'Voltagem';
	    //         $objAux['Localizacao'] = 'Localizacao';
		// 		$objAux['Enquadramento IPI'] = 'Enquadramento IPI';

	    //         $resultado->arrexcel[] = $objAux;

		//     	foreach ($res->produtos as $prod)
		//     	{
		//     		$objAux = new stdClass();
	    //         	$objAux = json_decode(json_encode($objAux), true);	    
		    		
		//     		$prod->VOLTAGEM = $classFrmcadproduto->retornaVoltagem($prod->VOLTAGEM);

		//     		$objAux['Marca'] = $prod->MARCA; 
	    //             $objAux['Codigo'] = $prod->CODIGO; 
	    //             $objAux['Referencia'] = $prod->REFERENCIA; 
	    //             $objAux['Descricao'] = $prod->DESCRICAO; 
	    //             $objAux['Modelo'] = $prod->MODELO;
	    //             $objAux['Estoque'] = number_format($prod->ESTOQUE_DISPONIVEL, 2, ',', '.');
	    //             $objAux[$valor_venda] = number_format($prod->VALORVENDA, 2, ',', '.');
	    //             $objAux['Cor'] = substr($prod->COR, 0, 50);
	    //             $objAux['Voltagem'] = $prod->VOLTAGEM;
	    //             $objAux['Localizacao'] = $prod->LOCALIZACAO;
		// 			$objAux['Enquadramento IPI'] = $prod->ENQUADRAMENTO_IPI;

	    //             $resultado->arrexcel[] = $objAux;
		//     	}
		//     }
	    // }

        $resultado->retorno = "OK";
        return $this->response->withJson($resultado);
    });

    class frmcadproduto
    {
    	function getProdutos($db, $params, $codempresa, $colunas)
    	{
    		$resultado = new stdClass();

			$empresas = getEmpresas($db, $codempresa);

			$sqlSalEst = "SELECT SALDO_ESTOQUE_ME FROM parametros_multiempresas WHERE COD_EMPRESA = :codempresa";
			$sthSalEst = $db->prepare($sqlSalEst);
			try {
				$sthSalEst->bindValue("codempresa", $codempresa);
				$sthSalEst->execute();
			} catch(PDOException $e) {
				$resultado->retorno = "ERRO";
				$resultado->detalhes = $e->getMessage();
				return $this->response->withJson($resultado);
			}
			$salEstMe = $sthSalEst->fetch(PDO::FETCH_OBJ);

			$arrEmpresas = array();
			
			if ($salEstMe->SALDO_ESTOQUE_ME != 1) {
				$arrEmpresas[] = $codempresa;
			} else {
				if (sizeof($empresas) > 1) {
					foreach ($empresas as $emp) {                
						$arrEmpresas[] = $emp->COD_EMPRESA;
					}
				} else {
					$arrEmpresas[] = $codempresa;
				}
			}

	        $condicao = $params['condicao'];
			if($salEstMe->SALDO_ESTOQUE_ME == 0) {
				if($params['condest'] == 'true') {
					$condicao .= " AND estp.DISPONIVEL > 0";                
				}
				if($params['condestoquemaismenos'] == 'true') {
					$condicao .= " AND estp.DISPONIVEL <> 0";
				}
			}
	        $parametro = $params['parametro'];
	        $info = $params['info'];
	        $quecontem = $params['quecontenha'];
	        $promocao = $params['promocao'];

	        if (trim($info) == '') {
	            $info = null;
	        }

	        if (trim($parametro) == '') {
	            $parametro = null;
	        }

			if($parametro == "N_TABELA"){
				$parametro = "CODIGOTABELAPRECO";
			}

			$empresaMatriz = $codempresa;
			$empresas = getEmpresas($db, $codempresa);
			if(isset($empresas[0]->COD_EMPRESA)){
				$empresaMatriz = $empresas[0]->COD_EMPRESA;
			}

	        // Trata os parametros recebidos para montar a condição
	        if(!is_null($parametro) and !is_null($info))
	        {
	            if($parametro != "BUSCA_CADASTRO_PRODUTO_INICIAL")
	            {
	               if (in_array($parametro, array('DESCRICAOCOMERCIAL','DESCRICAO','REFERENCIA', 'SERIE')))
	                {
	                    //$condicao .= " AND p.$parametro LIKE '%" . preg_replace('/\s+/', '%', $info) . "%' ";
	                    if($quecontem == true)
	                    {
	                        if($parametro == "SERIE")
	                            $condicao .= " AND sp.$parametro LIKE '%" . $info . "%' ";
							else if($parametro == "CODIGOTABELAPRECO"){
								$condicao .= " AND pp1.CODIGO = $info";
							}
	                        else
	                            $condicao .= " AND p.$parametro LIKE '%" . $info . "%' ";
	                    }
	                    else
	                    {
	                        if($parametro == "SERIE")
	                            $condicao .= " AND sp.$parametro LIKE '" . $info . "%' ";
							else if($parametro == "CODIGOTABELAPRECO"){
								$condicao .= " AND pp1.CODIGO = $info";
							}
	                        else
	                            $condicao .= " AND p.$parametro LIKE '" . $info . "%' ";
	                    }
	                }
					else if($parametro == "CODIGOTABELAPRECO"){
						$condicao .= " AND pp1.CODIGO = $info";
					}
	                else
	                {
	                    $condicao .= " and p.$parametro = '$info'";
	                }
	            }
	        }

			if($parametro == "CODIGOTABELAPRECO"){
				$parametro = "CODIGO";
			}

	        // Monta a condição do tipo de preço do produto
	        $cp = '';
	        

	        if($parametro == "BUSCA_CADASTRO_PRODUTO_INICIAL")
	        {
	            $classProduto = new produto();

	            $prods = $classProduto->buscaInicialCadastroProduto($db, $codempresa);
	            if($prods->retorno != "OK")
	            {
	                $resultado->retorno = "ERRO";
	                $resultado->detalhes = $prods->detalhes;
	                return $resultado;
	            }

	            $resultado->totalint = sizeof($prods->detalhes);

	            if($resultado->totalint == 0)
	            {
	                $resultado->retorno = "OK";
	                $resultado->produtos = array();
	                return $resultado;
	            }

	            $condicao .= " AND p.CODIGO IN(" . implode(',', $prods->detalhes) . ") ";
	        }

			$condicao = str_replace("and fm.COD_EMPRESAFORNECEDOR = 90", "and fm.COD_EMPRESAFORNECEDOR = 81", $condicao);

	        $sql = "SELECT DISTINCT ";
			$sql .= join(",", $colunas);
	        $sql .= " FROM produto p
	        JOIN      grupo        g  ON p.COD_GRUPO    = g.CODIGO AND p.COD_EMPRESA      = g.COD_EMPRESA
	        JOIN      subgrupo     s  ON p.COD_SUBGRUPO = s.CODIGO AND p.COD_EMPRESA      = s.COD_EMPRESA
	        LEFT JOIN marca        m  ON p.COD_MARCA    = m.CODIGO AND p.COD_EMPRESAMARCA = m.COD_EMPRESA
	        LEFT JOIN linhaproduto l  ON p.COD_LINHA    = l.CODIGO AND p.COD_EMPRESALINHA = l.COD_EMPRESA
	        LEFT JOIN unidademedida u ON p.COD_UNIDADE  = u.CODIGO AND p.COD_EMPRESA      = u.COD_EMPRESA    
	        LEFT OUTER JOIN estoque_produto estp ON estp.COD_EMPRESA = {$codempresa} AND estp.COD_PRODUTO = p.CODIGO
	        LEFT OUTER JOIN precoproduto pp ON pp.COD_PRODUTO = p.CODIGO AND pp.COD_EMPRESAREFERENCIA     = p.COD_EMPRESA AND pp.COD_EMPRESA = p.COD_EMPRESA
	        AND pp.PADRAO = 'S' 
			LEFT JOIN precoproduto pp1 ON pp1.COD_PRODUTO = p.CODIGO
			AND pp1.COD_EMPRESAREFERENCIA = p.COD_EMPRESA
			AND pp1.COD_EMPRESA = p.COD_EMPRESA
			LEFT JOIN empresa emp ON (emp.CODIGO = {$codempresa}) 
			LEFT JOIN empresa empm ON (p.COD_EMPRESA = empm.CODIGO) 
	        LEFT OUTER JOIN aliquota  origem ON origem.CODIGO    = p.COD_ALIQUOTAORIGEM AND origem.COD_EMPRESA   = p.COD_EMPRESAALIQUOTAORIGEM
	        LEFT OUTER JOIN aliquota    icms ON icms.CODIGO      = p.COD_ALIQUOTA       AND icms.COD_EMPRESA     = p.COD_EMPRESAALIQUOTAINTERNA
	        LEFT OUTER JOIN fornecedormarcas fm ON p.COD_EMPRESA = fm.COD_EMPRESA AND p.COD_MARCA = fm.COD_MARCA AND p.COD_EMPRESAMARCA = fm.COD_EMPRESAMARCA
	        LEFT OUTER JOIN fornecedor f ON fm.COD_EMPRESAFORNECEDOR = f.COD_EMPRESA AND fm.COD_FORNECEDOR = f.CODIGO 
	        LEFT OUTER JOIN (SELECT CODIGO, COD_EMPRESA, COD_PRODUTO, VALOR_PROMOCAO, INICIO_PROMOCAO, FIM_PROMOCAO, LANC_PROMOCAO FROM precoproduto WHERE COD_EMPRESA = {$codempresa}) AS pp2 ON pp2.COD_EMPRESA = {$codempresa} AND pp2.COD_PRODUTO = p.CODIGO AND pp.CODIGO = pp2.CODIGO 
	        LEFT OUTER JOIN (SELECT COD_PRODUTO, COD_EMPRESA, SERIE FROM serie_produto WHERE COD_EMPRESA = {$codempresa} GROUP BY COD_PRODUTO, SERIE) AS sp ON sp.COD_EMPRESA = {$codempresa} AND sp.COD_PRODUTO = p.CODIGO
        	LEFT OUTER JOIN local_estoque_produto lep ON (lep.COD_EMPRESAREFERENCIA = p.COD_EMPRESA and lep.COD_PRODUTO = p.CODIGO and lep.COD_EMPRESA = {$codempresa} and lep.DISPONIVEL <> 0) ";
	        $empresas = getEmpresas($db, $codempresa);
	        if(is_array($empresas) && $empresas != null && sizeof($empresas) > 1) {
	            foreach ($empresas as $emp)
	            {
	                $a_emp[] = $emp->COD_EMPRESA;
	            }

	            $sql .= (count($a_emp)==1)  ? " WHERE p.COD_EMPRESA = " . $a_emp[0]
	            : " WHERE p.COD_EMPRESA IN(" . implode(',', $a_emp) . ')';

	            $resultado->relacionamento = 1;
	        } else {
	            $sql .= " where p.COD_EMPRESA = ".$codempresa;

	            $resultado->relacionamento = 0;
	        }

			if($promocao)
			{
				$sql .= " AND pp2.LANC_PROMOCAO IS NOT NULL AND '".date("Y-m-d")."' >= pp2.INICIO_PROMOCAO AND '".date("Y-m-d")."' <= pp2.FIM_PROMOCAO ";
			}

			$sql .= ' '.$condicao;

	        if($parametro == "SERIE")
	            $sql .= ' group by p.CODIGO order by sp.'.$parametro;
	        else
	            $sql .= ' group by p.CODIGO order by p.'.($parametro == "BUSCA_CADASTRO_PRODUTO_INICIAL" ? "CODIGO" : $parametro);

			$sth = $db->prepare($sql);
			try {
				$sth->execute();
			} catch(PDOException $e) {
				$resultado->retorno = "ERRO";
				$resultado->detalhes = $e->getMessage();
				return $resultado;
			}

			$qntdImagens = 0;
			$obj = array();
	        while ($dados = $sth->fetch(PDO::FETCH_OBJ)) {
				if ($salEstMe->SALDO_ESTOQUE_ME == 1) {
					$dados->ESTOQUE_DISPONIVEL = 0;
					$sqlEstoqueTotal = "SELECT 
						ROUND(COALESCE(ep.DISPONIVEL, 0), 2) AS ESTOQUE_DISPONIVEL, 
						IF(ROUND(COALESCE(ep.DISPONIVEL, 0),2) < 0, 0, ROUND(COALESCE(ep.DISPONIVEL, 0),2)) AS ESTOQUE_DISPONIVEL_PREVISTO,
						ROUND(COALESCE(ep.COMPRADO, 0),2) AS ESTOQUE_COMPRADO_PREVISTO,
						ROUND(COALESCE(ep.VENDIDO, 0),2) AS ESTOQUE_VENDIDO_PREVISTO
					FROM estoque_produto ep  
					WHERE ep.COD_EMPRESA IN (".implode($arrEmpresas, ',').") AND 
						ep.COD_PRODUTO = :codproduto";
					$sthEstoqueTotal = $db->prepare($sqlEstoqueTotal);
					try {
						$sthEstoqueTotal->bindValue("codproduto", $dados->CODIGO);
						$sthEstoqueTotal->execute();
					} catch(PDOException $e) {
						$resultado->retorno = "ERRO";
						$resultado->detalhes = $e->getMessage();
						return $this->response->withJson($resultado);
					}
	
					while ($objEstoque = $sthEstoqueTotal->fetch(PDO::FETCH_OBJ)) {
						$dados->ESTOQUE_DISPONIVEL += $objEstoque->ESTOQUE_DISPONIVEL;
					}
				}
	            $dados->VALORVENDA = $dados->VALORDEVENDA;

	            $cst_tabela_a = strval($dados->COD_CST_TABELA_A);
				$cst_tabela_b = (strval($dados->COD_CST_TABELA_B) != "0" ? strval($dados->COD_CST_TABELA_B) : strval($dados->COD_CST_TABELA_B)."0");
				$dados->CST = (string) $cst_tabela_a.$cst_tabela_b;

	            if($empresaMatriz != $codempresa) {
	                
	                if($dados->PRECO_UNICO == 0 && $dados->PRECO_UNICO_MATRIZ == 0) {
	                    $sqlpreco = "Select VALORVENDA From precoproduto Where COD_EMPRESA = :codempresa and COD_PRODUTO = :codproduto";
	                    $sth2 = $db->prepare($sqlpreco);
	                    $sth2->bindValue("codempresa", $codempresa);
	                    $sth2->bindValue("codproduto", $dados->CODIGO);
	                    $sth2->execute();
	                    if($objpreco = $sth2->fetch(PDO::FETCH_OBJ)) {
	                        $dados->VALORVENDA = $objpreco->VALORVENDA;
	                    } else {
	                        $dados->VALORVENDA = 0;
	                    }
	                }
	            }                          

	            $cellVariants = new stdClass();
	            if($dados->CODIGOTABELAPRECO > 0 && $dados->LANC_PROMOCAO != null) 
	            {
	                $dataHoje = date("Y-m-d");
	                if($dataHoje >= $dados->INICIO_PROMOCAO && $dataHoje <= $dados->FIM_PROMOCAO)
	                {
	                    $dados->VALORDEVENDA = $dados->VALOR_PROMOCAO;
	                    $dados->VALORVENDA = $dados->VALOR_PROMOCAO;
	                    $cellVariants->VALORDEVENDA = "danger";
	                    $cellVariants->VALORVENDA = "danger";
	                }
	            }

	            $dados->_cellVariants = $cellVariants;

				$dados->PERC_DIF_ICMS = 0;
				if ($dados->DIFERENCAICMSCUSTO > 0) {
					$valorDescontoCusto = 0;
					if ($dados->DESCONTOCUSTO > 0) {
						$valorDescontoCusto = $dados->VALORCOMPRA * ($dados->DESCONTOCUSTO / 100);
					}

					$valorIpiCusto = 0;
					if ($dados->IPICUSTO > 0) {
						$valorIpiCusto = ($dados->VALORCOMPRA - $valorDescontoCusto) * ($dados->IPICUSTO / 100);
					}

					if (($dados->VALORCOMPRA - $valorDescontoCusto + $valorIpiCusto) > 0) {
						$dados->PERC_DIF_ICMS = ($dados->DIFERENCAICMSCUSTO / ($dados->VALORCOMPRA - $valorDescontoCusto + $valorIpiCusto)) * 100;
					}
				}

				if($dados->IMAGEM != "") {
					$qntdImagens += 1;
				}

				if($params['condest'] == 'true') {
					if($dados->ESTOQUE_DISPONIVEL > 0) {
						$obj[] = $dados;
					}                
				} else if($params['condestoquemaismenos'] == 'true') {
					if($dados->ESTOQUE_DISPONIVEL != 0) {
						$obj[] = $dados;
					}
				} else {
					$obj[] = $dados;
				}	            
	        }

			$resultado->qtdeimagens = $qntdImagens;
            $resultado->produtos = isset($obj) ? $obj : array();
            $resultado->retorno = "OK";
            return $resultado;
    	}

    	function retornaVoltagem($vol)
    	{
    		switch ($vol) {
    			case 0:
    				$vol = "NENHUMA";
    				break;    			
    			case 1:
    				$vol = "110";
    				break;  			
    			case 2:
    				$vol = "220";
    				break;  			
    			case 3:
    				$vol = "110/220";
    				break;  			
    			case 4:
    				$vol = "BIVOLT";
    				break;
				case 5:
    				$vol = "12";
    				break;
				case 6:
    				$vol = "24";
    				break;
				case 7:
    				$vol = "48";
    				break;
				case 8:
    				$vol = "127";
    				break;		
    			default:
    				$vol = "";
    				break;
    		}

    		return $vol;
    	}
    }

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
			$i = 0;
			if($number <= 26) {
				return $a[$i];
			}

			$repeat = floor(($number - 1) / 26);

			$i = ($number - 1) % 26;

			return str_repeat('A', $repeat) . $a[$i];
		}
	}

?>
