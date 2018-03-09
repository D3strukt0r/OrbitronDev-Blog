<?php

use EasyCorp\Bundle\EasyDeployBundle\Deployer\DefaultDeployer;

return new class extends DefaultDeployer
{
    public function configure()
    {
        return $this->getConfigBuilder()
            ->server('user@hostname')
            ->deployDir('/var/www/service-blog')
            ->repositoryUrl('git@github.com:OrbitronDev/service-blog.git')
            ->repositoryBranch('master')
            ;
    }

    public function beforeFinishingDeploy()
    {
        // Get composer
        $this->runRemote('php -r "copy(\'https://getcomposer.org/installer\', \'composer-setup.php\');"');
        $this->runRemote('php -r "if (hash_file(\'SHA384\', \'composer-setup.php\') === \'544e09ee996cdf60ece3804abc52599c22b1f40f4323403c44d44fdfdd586475ca9813a858088ffbc1f233e9b180f061\') { echo \'Installer verified\'; } else { echo \'Installer corrupt\'; unlink(\'composer-setup.php\'); } echo PHP_EOL;"');
        $this->runRemote('php ./composer-setup.php');
        $this->runRemote('php -r "unlink(\'composer-setup.php\');"');

        // Install dependencies
        $this->runRemote('php ./composer.phar install --no-dev --optimize-autoloader');
    }
};
