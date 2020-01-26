<?php

namespace App;

class OAuthCallback
{
	public function handle()
	{
		return [
			'request' => $_REQUEST,
		];
	}
}
