<?php

use Psr\Http\Message\ServerRequestInterface;
use React\EventLoop\Factory;
use React\Http\Response;
use React\Http\Server;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

require __DIR__ . '/vendor/autoload.php';

$loader = new FilesystemLoader(__DIR__ . '/templates');
$twig = new Environment($loader);
$markdown = new Parsedown();

$loop = Factory::create();
$server = new Server(function (ServerRequestInterface $request) use ($twig, $markdown) {

  $path = $request->getUri()->getPath();
  $path_info = pathinfo($path);
  $body = '';
  if (empty($path_info['extension'])) {
    if ($path == '/') {
      $content_file = 'index.html';
    }
    else {
      $content_file = $path . '.html';
    }
    $content = file_get_contents('./contents/' . $content_file);
    $body = $twig->render('html.html.twig', ['content' => $content]);
  }

  return new Response(
    200,
    [
      'Content-Type' => 'text/html',
    ],
    $body
  );
});
$socket = new \React\Socket\Server('127.0.0.1:9991', $loop);
$server->listen($socket);
echo 'Listening on ' . str_replace('tcp:', 'http:', $socket->getAddress()) . PHP_EOL;
$loop->run();