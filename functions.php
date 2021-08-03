<?php

require_once 'files/classes/Request.php';

function SpiceChecker_getUserSpiceGitHubFileTree()
{
    $return = [];
    $return['state'] = false;

    $url = 'https://api.github.com/repos/mudmin/userspice5/git/trees/master?recursive=1';
    $request = new \JJG\Request($url);
    $request->execute();

    if ($request->getHttpCode() == 200) {
        $response = json_decode($request->getResponse());
        $return['state'] = true;
        $return['data'] = $response;

        return $return;
    } else {
        $return['error'] = 'non_200_response';

        return $return;
    }

    return $return;
}

function SpiceChecker_getDirContents($dir, &$results = [])
{
    $files = scandir($dir);

    foreach ($files as $key => $value) {
        $path = realpath($dir.DIRECTORY_SEPARATOR.$value);
        if (!is_dir($path)) {
            $results[] = $path;
        } elseif ($value != '.' && $value != '..') {
            SpiceChecker_getDirContents($path, $results);
            $results[] = $path;
        }
    }

    return $results;
}
