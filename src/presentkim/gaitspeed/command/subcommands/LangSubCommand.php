<?php

namespace presentkim\gaitspeed\command\subcommands;

use pocketmine\command\CommandSender;
use presentkim\gaitspeed\{
  GaitSpeedMain as Plugin, util\Translation, command\SubCommand
};

class LangSubCommand extends SubCommand{

    public function __construct(Plugin $owner){
        parent::__construct($owner, Translation::translate('prefix'), 'command-gaitspeed-lang', 'gaitspeed.lang.cmd');
    }

    /**
     * @param CommandSender $sender
     * @param array         $args
     *
     * @return bool
     */
    public function onCommand(CommandSender $sender, array $args){
        if (isset($args[0]) && is_string($args[0]) && ($args[0] = strtolower(trim($args[0])))) {
            $resource = $this->owner->getResource("lang/$args[0].yml");
            if (is_resource($resource)) {
                $dataFolder = $this->owner->getDataFolder();
                if (!file_exists($dataFolder)) {
                    mkdir($dataFolder, 0777, true);
                }

                stream_copy_to_stream($resource, $fp = fopen("{$dataFolder}lang.yml", "wb"));
                fclose($fp);
                Translation::loadFromResource($resource);

                $sender->sendMessage($this->prefix . Translation::translate($this->getFullId('success'), $args[0]));
            } else {
                $sender->sendMessage($this->prefix . Translation::translate($this->getFullId('failure'), $args[0]));
            }
            return true;
        } else {
            return false;
        }
    }
}