<?php

namespace Deployer;

require 'recipe/laravel.php';
// Project name
set('application', 'Proxy API');
// Project repository
set('repository', 'git@github.com:yoramdelangen/proxy-api.git');
// [Optional] Allocate tty for git clone. Default value is false.
// set('git_tty', true);
set('ssh_multiplexing', true);
// set('use_relative_symlink', false);
// Shared files/dirs between deploys
add('shared_files', ['.env']);
// add('shared_dirs', ['storage']);
// Writable dirs by web server
// add('writable_dirs', ['storage', 'public']);
// Hosts
host('ssh.sydl.nl')
    ->stage('production')
    ->user('yoram')
    ->set('deploy_path', '/var/www/apps/proxy-api')
    ->set('keep_releases', 2)
    ->set('branch', 'master');

// Tasks
task('deploy', [
    'deploy:prepare',
    'deploy:lock',
    'deploy:release',
    'deploy:update_code',
    'deploy:shared',
    'deploy:writable',
    'deploy:vendors',
    'deploy:symlink',
    'deploy:unlock',
    'cleanup',
    'success',
]);

// [Optional] if deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');
// Migrate database before symlink new release.
// before('deploy:symlink', 'artisan:migrate');
