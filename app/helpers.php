<?php

function storage_path(string $path)
{
	return realpath(realpath(__DIR__.'/../storage/').'/'. $path);
}