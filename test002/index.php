<?php

require 'vendor/autoload.php';

$config['displayErrorDetails'] = true;
$config['addContentLengthHeader'] = false;

$config['db']['host']   = "localhost";
$config['db']['user']   = "root";
$config['db']['pass']   = "W3nj1t0_";
$config['db']['dbname'] = "test";

$app = new Slim\App(["settings" => $config]);
$container = $app->getContainer();

$container['logger'] = function($c) {
    $logger = new \Monolog\Logger('my_logger');
    $file_handler = new \Monolog\Handler\StreamHandler("../logs/app.log");
    $logger->pushHandler($file_handler);
    return $logger;
};

$container['db'] = function ($c) {
    $db = $c['settings']['db'];
    $pdo = new PDO("mysql:host=" . $db['host'] . ";dbname=" . $db['dbname'],
        $db['user'], $db['pass']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    return $pdo;
};

$app->options('/{routes:.+}', function ($request, $response, $args) {
    return $response;
});

$app->add(function ($req, $res, $next) {
    $response = $next($req, $res);
    return $response
            ->withHeader('Access-Control-Allow-Origin', 'http://mysite')
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
});

$app->map(['GET', 'POST'], '/blog/{a}/{b}', function ($request, $response, $args) {
    return $response->write("Portada " . $args['a']);
});

$app->get('/', function ($request, $response, $args) {
	$sth = $this->db->prepare("SELECT * FROM articulo WHERE codigo = '7801610001295'");
	$sth->execute();
	$todos = $sth->fetchAll();
	return $this->response->withJson($todos);
});

$app->get('/hello/{name}', function ($request, $response, $args) {
    return $response->write("Hello, " . $args['name']);
});

$app->get('/todos', function ($request, $response, $args) {
	$sth = $this->db->prepare("SELECT * FROM articulo");
	$sth->execute();
	$todos = $sth->fetchAll();
	return $this->response->withJson($todos);
});

$app->get('/todo/[{codigo}]', function ($request, $response, $args) {
	$sth = $this->db->prepare("SELECT * FROM articulo WHERE codigo = :codigo");
	$sth->bindParam("codigo", $args['codigo']);
	$sth->execute();
	$todos = $sth->fetchObject();
	return $this->response->withJson($todos);
});

$app->get('/todos/search/[{query}]', function ($request, $response, $args) {
	$sth = $this->db->prepare("SELECT * FROM articulo WHERE UPPER(articulo) LIKE :query ORDER BY articulo");
	$query = "%".$args['query']."%";
	$sth->bindParam("query", $query);
	$sth->execute();
	$todos = $sth->fetchAll();
	return $this->response->withJson($todos);
});

$app->post('/todo', function ($request, $response) {
	$input = $request->getParsedBody();
	$sql = "INSERT INTO opcion (id_padre, nombre, formulario) VALUES (:id_padre, :nombre, :formulario)";
	$sth = $this->db->prepare($sql);
	$sth->bindParam("id_padre", $input['id_padre']);
	$sth->bindParam("nombre", $input['nombre']);
	$sth->bindParam("formulario", $input['formulario']);
	$sth->execute();
	$input['id'] = $this->db->lastInsertId();
	return $this->response->withJson($input);
});

$app->put('/todo/[{id}]', function ($request, $response, $args) {
	$input = $request->getParsedBody();
	$sql = "UPDATE opcion SET nombre = :nombre, formulario = :formulario WHERE id_opcion = :id";
	$sth = $this->db->prepare($sql);
	$sth->bindParam("id", $args['id']);
	$sth->bindParam("nombre", $input['nombre']);
	$sth->bindParam("formulario", $input['formulario']);
	$sth->execute();
	$input['id'] = $args['id'];
	return $this->response->withJson($input);
});

$app->delete('/todo/[{id}]', function ($request, $response, $args) {
	$sth = $this->db->prepare("DELETE FROM opcion WHERE id_opcion = :id");
	$sth->bindParam("id", $args['id']);
	$sth->execute();
	$input['id'] = $args['id'];
	return $this->response->withJson($input);
});

$app->run();