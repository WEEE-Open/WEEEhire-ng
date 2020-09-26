<?php


namespace WEEEOpen\WEEEHire;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\Diactoros\Response\HtmlResponse;

class PageIndex implements RequestHandlerInterface {
	public function handle(ServerRequestInterface $request): ResponseInterface {
		$template = Template::create($request->getUri());

		$db = new Database();

		$expiry = $db->getConfigValue('expiry');

		// Get from DB -> if "unixtime.now >= expiry date" then candidate_close : else show the form
		if($expiry !== null && time() >= $expiry) {
			return new HtmlResponse($template->render('candidate_close'));
		} else {
			return new HtmlResponse($template->render('index', ['expiry' => $expiry]));
		}
	}
}
