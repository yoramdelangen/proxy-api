<?php

namespace Utils;

use Illuminate\Database\Capsule\Manager as Capsule;


class DB extends Capsule
{
	public static function connect(array $params): self
	{
		$_ = new static();
		$_->addConnection($params);
		return $_;
	}
}
