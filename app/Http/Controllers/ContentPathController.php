<?php

namespace App\Http\Controllers;

use App\Support\Routing\ContentPaths;

class ContentPathController extends Controller
{
    public function __invoke(ContentPaths $paths, string $path): mixed
    {
        $page = $paths->resolve($path);

        abort_if($page === null, 404);

        return app()->call($page);
    }
}
