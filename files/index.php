<?php

require_once '../../../../users/init.php';
require_once $abs_us_root.$us_url_root.'users/includes/template/prep.php';

if (!pluginActive('spiceinspector')) {
    Redirect::to("{$us_url_root}users/admin.php?view=plugins&err=Plugin is not activated");
    exit();
}

if (!hasPerm(2)) {
    require_once $abs_us_root.$us_url_root.'usersc/scripts/did_not_have_permission.php';
    exit();
}

$github_files = SpiceChecker_getUserSpiceGitHubFileTree();
if ($github_files['state']) {
    $users_files = [];
    $excluded_folders = [
      'users/lang/',
      'users/js/',
      'users/_blank_pages/',
      'users/init.php',
    ];

    foreach ($github_files['data']->tree as $github_file) {
        if ($github_file->type != 'blob') {
            continue;
        }

        foreach ($excluded_folders as $ef) {
            if (substr($github_file->path, 0, strlen($ef)) === $ef) {
                continue 2;
            }
        }

        if (substr($github_file->path, 0, 6) === 'users/') {
            $users_files[] = $github_file->path;
        }
    }

    $system_folder = "{$abs_us_root}{$us_url_root}";
    $system_files_unclean = SpiceChecker_getDirContents("{$system_folder}users/");
    $extra_files = [];
    foreach ($system_files_unclean as $sf) {
        if (is_dir($sf)) {
            continue;
        }

        $sf = str_replace('\\', '/', $sf);
        $sf = str_replace($system_folder, '', $sf);

        foreach ($excluded_folders as $ef) {
            if (substr($sf, 0, strlen($ef)) === $ef) {
                continue 2;
            }
        }

        if (!in_array($sf, $users_files)) {
            $extra_files[] = $sf;
        }
    }
} else {
    $error = $github_files['error'] ?? 'unknown_error';
    $errors[] = "We were unable to retrieve the files from the GitHub API: {$error}";
}

?>
<div class="container">
  <h1>Spice Checker</h1>
  <p><a href="<?php echo $us_url_root; ?>users/admin.php">[back to admin panel]</a></p>
  <p>The following tool will display files in your <strong>users/</strong> directory that are extra in comparison to the <a href="https://github.com/mudmin/userspice5" target="_blank">UserSpice GitHub Repo</a>.</p>
  <p>The following files and folders are excluded:<br>
    <ul>
      <?php foreach ($excluded_folders as $ef) { ?>
        <li><?php echo $ef; ?></li>
      <?php } ?>
    </ul>
  </p>
  <p>The following files were detected as extra in the <strong>users/</strong> folder in comparison to the GitHub Repo:<br>
    <ul>
      <?php foreach ($extra_files as $ef) { ?>
        <li><?php echo $ef; ?></li>
      <?php } ?>
    </ul>
  </p>
</div>
<?php require_once $abs_us_root.$us_url_root.'users/includes/html_footer.php';
