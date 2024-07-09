<?php

namespace WEEEOpen\WEEEHire;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\Response\RedirectResponse;

class PageStatus implements RequestHandlerInterface
{
	public function handle(ServerRequestInterface $request): ResponseInterface
	{
		// Page for candidates to see their own status (approved/rejected)

		$template = Template::create($request->getUri());
		$GET = $request->getQueryParams();
		if (!$GET['id'] || !$GET['token']) {
			return new HtmlResponse($template->render('error', ['message' => 'Missing id or token']), 400);
		}

		$db = new Database();

		try {
			$id = (int) $GET['id'];
			if ($db->validateToken($id, $GET['token'])) {
				$user = $db->getUser($id);
			} else {
				return new HtmlResponse($template->render('error', ['message' => 'Invalid id or token']), 404);
			}
		} catch (DatabaseException $e) {
			return new HtmlResponse($template->render('error', ['message' => 'Database error']), 500);
		}

		if (isset($GET['download'])) {
			// GDPR data download button
			$attributes = (array) $user;
			$downloadable = [
			'name',
			'surname',
			'degreecourse',
			'year',
			'matricola',
			'area',
			'letter'
			];
			// Filter out other attributes
			$attributes = array_intersect_key($attributes, array_combine($downloadable, $downloadable));

			$headers = [
			'Content-Transfer-Encoding' => 'Binary',
			'Content-Description' => 'File Transfer',
			'Content-Disposition' => 'attachment; filename=weeehire.json',
			];
			return new JsonResponse($attributes, 200, $headers, JsonResponse::DEFAULT_JSON_FLAGS | JSON_PRETTY_PRINT);
		} elseif (isset($GET['delete'])) {
			// Delete button
			try {
				$db->deleteUser($id);
				return new RedirectResponse('/', 303);
			} catch (DatabaseException $e) {
				return new HtmlResponse($template->render('error', ['message' => 'Database error']), 500);
			}
		}

		return new HtmlResponse($template->render('status', ['user' => $user]));
	}
}
