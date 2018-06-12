<?php

namespace Deployer;

require 'recipe/symfony4.php';

// Project name
set('application', 'blog');

// Environment vars
add('env', [
    'APP_ENV' => 'prod',
]);

// Project repository
set('repository', 'https://github.com/D3strukt0r/service-blog.git');
set('branch', 'master');

// [Optional] Allocate tty for git clone. Default value is false.
set('git_tty', true);

// Shared files/dirs between deploys
add('shared_files', []);
add('shared_dirs', ['var/data']);

// Writable dirs by web server
set('writable_dirs', []);

// Hosts
host('local')
    ->hostname('local')
    ->set('deploy_path', '/var/www/{{application}}')
    ->set('http_user', 'www-data')
    ->set('ssh_multiplexing', true);
host('remote')
    ->hostname('hostpoint')
    ->set('deploy_path', '/home/manuelev/www/{{application}}')
    ->set('http_user', 'manuelev')
    ->set('keep_releases', 1)
    ->set('bin/php', function () {
        return locateBinaryPath('/usr/local/php72/bin/php');
    });

// Tasks
task('build', function () {
    run('cd {{release_path}} && build');
});

// [Optional] if deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');

// Migrate database before symlink new release.
//before('deploy:symlink', 'database:migrate');
