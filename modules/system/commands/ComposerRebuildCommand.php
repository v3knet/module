<?php

namespace atsilex\module\system\commands;

use atsilex\module\system\ModularApp;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ComposerRebuildCommand extends Command
{

    /** @var  ModularApp */
    protected $app;

    public function __construct(ModularApp $app)
    {
        $this->app = $app;

        $vendor = isset($app['vendor_machine_name']) ? $app['vendor_machine_name'] : 'v3k';
        parent::__construct($vendor . ':composer-rebuild');
    }

    protected function configure()
    {
        $this->setDescription("Rebuild master composer file for all non-core modules.");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $root = $this->app->getAppRoot();
        $json = [];
        foreach ($this->app->getModules() as $name) {
            if (!$this->isCoreModule($name)) {
                $this->mergeModuleComposer($json, $name);
            }
        }

        file_put_contents($root . '/files/composer.json', json_encode((object) $json));
        passthru("composer --working-dir=$root/files update");
    }

    private function isCoreModule($name)
    {
        $ns = $this->app->getModule($name)->getNamespace();

        return !strpos($ns, 'v3knet\\module\\');
    }

    private function mergeModuleComposer(&$json, $name)
    {
        $module = $this->app->getModule($name);
        $moduleDir = $module->getPath();
        $moduleJson = "$moduleDir/composer.json";
        if (file_exists($moduleJson)) {
            $moduleJson = file_get_contents($moduleJson);
            $json = array_merge_recursive($json, json_decode($moduleJson, true));
        }
    }

}
