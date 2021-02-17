<?php

$router->any('/webdav/{path}?/{path2}?/{path3}?', [App\Webdav\Joplin::class, 'index']);
