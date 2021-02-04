<?php

use EasyCorp\Bundle\EasyDeployBundle\Deployer\DefaultDeployer;

return new class extends DefaultDeployer
{
    public function configure()
    {
        return $this->getConfigBuilder()
            // SSH connection string to connect to the remote server (format: user@host-or-IP:port-number)
            ->server($_ENV['DEPLOY_SERVER'])
            // set files as shared between the different deployments
            ->sharedFilesAndDirs(['.env.local', 'parameters.yaml'])
            // composer is searched using which so don't need the full path
            ->remoteComposerBinaryPath('composer')
            // phpcs:ignore Generic.Files.LineLength.TooLong
            ->composerInstallFlags('--no-dev --prefer-dist --no-interaction --quiet --no-scripts && composer post-scripts')
            // the absolute path of the remote server directory where the project is deployed
            ->deployDir($_ENV['DEPLOY_DIR'])
            // the URL of the Git repository where the project code is hosted
            ->repositoryUrl($_ENV['DEPLOY_REPOSITORY_URL'])
            // the repository branch to deploy
            ->repositoryBranch($_ENV['DEPLOY_REPOSITORY_BRANCH'])
        ;
    }

    // run some local or remote commands before the deployment is started
    public function beforeStartingDeploy()
    {
        // $this->runLocal('./vendor/bin/simple-phpunit');
    }

    // executed just before doing the composer install
    public function beforePreparing()
    {
        $this->runRemote('mkdir -p {{ deploy_dir }}/shared');
        $this->runRemote('cp {{ deploy_dir }}/repo/.env {{ project_dir }}/.env');
        $this->runRemote('cp {{ deploy_dir }}/.env.local {{ deploy_dir }}/shared/.env.local');
    }

    public function beforeOptimizing()
    {
        $this->runRemote('{{ project_dir }}/bin/console app:check:env');
    }

    // run some local or remote commands after the deployment is finished
    public function beforeFinishingDeploy()
    {
        // $this->runRemote('{{ console_bin }} app:my-task-name');
        // $this->runLocal('say "The deployment has finished."');
    }
};
