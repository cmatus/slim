<?php

$app->get('/clientes', function ($request, $response, $args) {
    $sth = $this->db->prepare("SELECT * FROM cliente;");
	$sth->execute();
	$retorno = $sth->fetchAll();
	return $this->response->withJson($retorno);
});