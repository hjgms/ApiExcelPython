<?php

class SendRequestApiExcel {
  
  private $api_url = 'http://localhost:4545';
  private $route_generate = '/excel/generate/';
  private $route_status = '/excel/status/';

  function getRequest($route, $data) {

    $ch = curl_init($api_url.$route);

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
      $request = $this->getRequest($route_generate, $data);

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
      $request = $this->getRequest($route_status, $data);

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
}