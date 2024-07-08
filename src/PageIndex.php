<?php

namespace WEEEOpen\WEEEHire;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\Diactoros\Response\HtmlResponse;

class PageIndex implements RequestHandlerInterface
{
	public function handle(ServerRequestInterface $request): ResponseInterface
	{
		$template = Template::create($request->getUri());

		$db = new Database();

		$expiry = $db->getConfigValue('expiry');
		$positions = $db->getPositions();

		if (count($positions) === 0) {
			$expiry = 1;
		} else {
			// check that there is at least one position available
			$isAtLeastOneAvailable = false;
			for ($i = 0; $i < count($positions); $i++) {
				if ($positions[$i]['available'] == 1) {
					$isAtLeastOneAvailable = true;
					break;
				}
			}
			if (!$isAtLeastOneAvailable) {
				$expiry = 1;
			}
		}

		// Get from DB -> if "unixtime.now >= expiry date" then candidate_close : else show the form
		if ($expiry !== null && time() >= $expiry) {
			return new HtmlResponse($template->render('candidate_close'));
		} else {
			return new HtmlResponse($template->render('index', ['expiry' => $expiry]));
		}
	}
}
