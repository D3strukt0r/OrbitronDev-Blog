<?php

namespace Deployer;

require 'recipe/symfony4.php';

// Project name
set('application', 'blog');

// Environment vars
set('env', [
    'APP_ENV' => 'prod'
]);
set('composer_options', '{{composer_action}} --verbose --prefer-dist --no-progress --no-interaction --no-dev --optimize-autoloader  --ignore-platform-reqs');

// Project repository
set('repository', 'https://github.com/OrbitronDev/service-blog.git');
set('branch', 'master');

// [Optional] Allocate tty for git clone. Default value is false.
set('git_tty', true); 

// Shared files/dirs between deploys 
add('shared_files', ['.htaccess']);
add('shared_dirs', []);

// Writable dirs by web server 
//add('writable_dirs', []);
set('writable_dirs', []);

// Hosts
host('orbitrondev')
    ->set('deploy_path', '/home/u530305173/public_html/blog')
    ->set('http_user', 'u530305173');

// Tasks
task('build', function () {
    run('cd {{release_path}} && build');
});

// [Optional] if deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');

// Migrate database before symlink new release.
//before('deploy:symlink', 'database:migrate');
