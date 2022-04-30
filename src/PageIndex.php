<?php

namespace WEEEOpen\WEEEHire;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\Diactoros\Response\HtmlResponse;

require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'roles.php';

class PageIndex implements RequestHandlerInterface
{
	public function handle(ServerRequestInterface $request): ResponseInterface
	{
		$template = Template::create($request->getUri());

		$db = new Database();

		$expiry = $db->getConfigValue('expiry');
		$rolesUnavailable = $db->getConfigValue('rolesUnavailable');
		$rolesUnavailableCount = $rolesUnavailable ? count(explode('|', $rolesUnavailable)) : 0;
		$allRoles = count(getRoles());

		if ($rolesUnavailableCount === $allRoles) {
			$expiry = 1;
		}

		// Get from DB -> if "unixtime.now >= expiry date" then candidate_close : else show the form
		if ($expiry !== null && time() >= $expiry) {
			return new HtmlResponse($template->render('candidate_close'));
		} else {
			return new HtmlResponse($template->render('index', ['expiry' => $expiry]));
		}
	}
}
