<?php

/**
 * This file is part of Antilope
 *
 * Antilope is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * PHP version 7.4
 *
 * @package Antilope
 * @author Nicolas Peugnet <n.peugnet@free.fr>
 * @author Vincent Peugnet <vincent-peugnet@riseup.net>
 * @copyright 2021 Nicolas Peugnet, Vincent Peugnet
 * @license https://www.gnu.org/licenses/agpl-3.0.txt AGPL-3.0-or-later
 */

use EasyCorp\Bundle\EasyDeployBundle\Deployer\DefaultDeployer;

/**
 * This class is the config file used by EasyDeployBundle.
 *
 * It reads its own config through environment variables.
 * Here is the list of variables that can be set to configure it:
 * - DEPLOY_SERVER
 * - DEPLOY_DIR
 * - DEPLOY_REPOSITORY_URL
 * - DEPLOY_REPOSITORY_BRANCH
 * - DEPLOY_WEBSERVER
 * - DEPLOY_COMPOSER_PATH
 */
return new class extends DefaultDeployer
{

    protected String $branch = 'main';
    protected bool $isApache = false;
    protected String $compPath = 'composer';
    protected String $compFlags = '--no-interaction --quiet';
    protected String $compInstallFlags = '--no-dev --prefer-dist';
    protected String $compScriptsFlags = '--no-scripts && composer post-scripts';

    public function __construct()
    {
        $this->isApache = isset($_ENV['DEPLOY_WEBSERVER']) && $_ENV['DEPLOY_WEBSERVER'] == 'apache';
        if (!empty($_ENV['DEPLOY_COMPOSER_PATH'])) {
            $this->compPath = $_ENV['DEPLOY_COMPOSER_PATH'];
        }
        if (!empty($_ENV['DEPLOY_REPOSITORY_BRANCH'])) {
            $this->branch = $_ENV['DEPLOY_REPOSITORY_BRANCH'];
        }
    }

    public function configure()
    {
        return $this->getConfigBuilder()
            // SSH connection string to connect to the remote server (format: user@host-or-IP:port-number)
            ->server($_ENV['DEPLOY_SERVER'])
            // Forward SSH agent to use local keys
            ->useSshAgentForwarding(true)
            // set files as shared between the different deployments
            ->sharedFilesAndDirs(['.env.local', 'parameters.yaml'])
            // composer is searched using which so don't need the full path
            ->remoteComposerBinaryPath($this->compPath)
            // phpcs:ignore Generic.Files.LineLength.TooLong
            ->composerInstallFlags("$this->compFlags $this->compInstallFlags $this->compScriptsFlags")
            // the absolute path of the remote server directory where the project is deployed
            ->deployDir($_ENV['DEPLOY_DIR'])
            // the URL of the Git repository where the project code is hosted
            ->repositoryUrl($_ENV['DEPLOY_REPOSITORY_URL'])
            // the repository branch to deploy
            ->repositoryBranch($this->branch)
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
        $this->runRemote('ln -f {{ deploy_dir }}/.env.local {{ deploy_dir }}/shared/.env.local');
    }

    public function beforeOptimizing()
    {
        $this->runRemote('{{ console_bin }} app:check:env');
        if ($this->isApache) {
            $this->runRemote("$this->compPath require symfony/apache-pack $this->compFlags $this->compScriptsFlags");
        }
    }

    public function beforePublishing()
    {
        $this->runRemote("$this->compPath dump-env prod");
    }

    public function beforeFinishingDeploy()
    {
        $this->runRemote('{{ console_bin }} doctrine:database:create --if-not-exists');
        $this->runRemote('{{ console_bin }} doctrine:migrations:migrate');
    }
};
