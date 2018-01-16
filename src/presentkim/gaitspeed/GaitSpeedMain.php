<?php

namespace presentkim\gaitspeed;

use pocketmine\command\{
  CommandExecutor, PluginCommand
};
use pocketmine\entity\Attribute;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use presentkim\gaitspeed\{
  listener\PlayerEventListener, command\CommandListener, util\Translation
};
use function presentkim\gaitspeed\util\extensionLoad;

class GaitSpeedMain extends PluginBase{

    /** @var self */
    private static $instance = null;

    /** @var PluginCommand[] */
    private $commands = [];

    /** @return self */
    public static function getInstance(){
        return self::$instance;
    }

    public function onLoad(){
        if (self::$instance === null) {
            // register instance
            self::$instance = $this;

            // load utils
            $this->getServer()->getLoader()->loadClass('presentkim\gaitspeed\util\Utils');

            // load default lang
            Translation::loadFromResource($this->getResource('lang/eng.yml'), true);

            // Dispose of existing data
            $sqlite3Path = "{$this->getDataFolder()}data.sqlite3";
            if (file_exists($sqlite3Path)) {
                extensionLoad('sqlite3');

                $db = new \SQLITE3($sqlite3Path);
                $results = $db->query("SELECT * FROM gait_speed_list;");
                $config = $this->getConfig();
                $playerData = [];
                while ($result = $results->fetchArray(SQLITE3_NUM)) {
                    $key = mb_convert_encoding($result[0], "ASCII", "UTF-8");
                    $value = mb_convert_encoding($result[1], "ASCII", "UTF-8");
                    $playerData[$key] = $value;
                }
                $config->set('playerData', $playerData);
                $this->saveConfig();
                unset($db, $results, $result);
                unlink($sqlite3Path);
            }
        }
    }

    public function onEnable(){
        $this->load();

        // register event listeners
        $this->getServer()->getPluginManager()->registerEvents(new PlayerEventListener(), $this);
    }

    public function onDisable(){
        $this->save();
    }

    public function load(){
        $dataFolder = $this->getDataFolder();
        if (!file_exists($dataFolder)) {
            mkdir($dataFolder, 0777, true);
        }

        $this->reloadConfig();

        // load lang
        $langfilename = $dataFolder . 'lang.yml';
        if (!file_exists($langfilename)) {
            $resource = $this->getResource('lang/eng.yml');
            Translation::loadFromResource($resource);
            stream_copy_to_stream($resource, $fp = fopen("{$dataFolder}lang.yml", "wb"));
            fclose($fp);
        } else {
            Translation::load($langfilename);
        }

        // unregister commands
        foreach ($this->commands as $command) {
            $this->getServer()->getCommandMap()->unregister($command);
        }
        $this->commands = [];

        // register commands
        $this->registerCommand(new CommandListener($this), Translation::translate('command-gaitspeed'), 'GaitSpeed', 'gaitspeed.cmd', Translation::translate('command-gaitspeed@description'), Translation::translate('command-gaitspeed@usage'), Translation::getArray('command-gaitspeed@aliases'));
    }

    public function save(){
        $dataFolder = $this->getDataFolder();
        if (!file_exists($dataFolder)) {
            mkdir($dataFolder, 0777, true);
        }

        // save db
        $this->saveConfig();
    }

    /**
     * @param CommandExecutor $executor
     * @param                 $name
     * @param                 $fallback
     * @param                 $permission
     * @param string          $description
     * @param null            $usageMessage
     * @param array|null      $aliases
     */
    private function registerCommand(CommandExecutor $executor, $name, $fallback, $permission, $description = "", $usageMessage = null, array $aliases = null){
        $command = new PluginCommand($name, $this);
        $command->setExecutor($executor);
        $command->setPermission($permission);
        $command->setDescription($description);
        $command->setUsage($usageMessage ?? ('/' . $name));
        if (is_array($aliases)) {
            $command->setAliases($aliases);
        }

        $this->getServer()->getCommandMap()->register($fallback, $command);
        $this->commands[] = $command;
    }

    /**
     * @param Player $player
     */
    public function applyTo(Player $player){
        $configData = $this->getConfig()->getAll();
        $player->getAttributeMap()->getAttribute(Attribute::MOVEMENT_SPEED)->setValue(($configData['playerData'][$player->getLowerCaseName()] ?? $configData['default-speed']) * 0.001);
    }
}
