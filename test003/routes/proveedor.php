<?php

$app->get('/proveedores', function ($request, $response, $args) {
    $sth = $this->db->prepare("SELECT * FROM proveedor;");
	$sth->execute();
	$retorno = $sth->fetchAll();
	return $this->response->withJson($retorno);
});