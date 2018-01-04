<?php

namespace presentkim\gaitspeed\command;

use pocketmine\command\CommandSender;
use presentkim\gaitspeed\{
  GaitSpeedMain as Plugin, util\Translation
};
use function presentkim\gaitspeed\util\in_arrayi;

abstract class SubCommand{

    /** @var Plugin */
    protected $owner;

    /** @var string */
    protected $prefix;

    /** @var string */
    protected $strId;

    /** @var string */
    protected $label;

    /** @var string[] */
    protected $aliases = [];

    /** @var string */
    protected $usage;

    /** @var string|null */
    protected $permission = null;

    /**
     * SubCommand constructor.
     *
     * @param Plugin $owner
     * @param string $prefix
     * @param string $strId
     * @param string $permission
     */
    public function __construct(Plugin $owner, string $prefix, string $strId, string $permission){
        $this->owner = $owner;
        $this->prefix = $prefix;
        $this->strId = $strId;

        $this->label = Translation::translate($this->strId);
        $this->aliases = Translation::getArray($this->getFullId('aliases'));
        $this->usage = Translation::translate($this->getFullId('usage'));

        $this->permission = $permission;
    }

    /**
     * @param CommandSender $sender
     * @param array         $args
     */
    public function execute(CommandSender $sender, array $args){
        if (!$sender->hasPermission("gaitspeed.$this->strId.cmd")) {
            $sender->sendMessage($this->prefix . Translation::translate('command-generic-failure@permission'));
        } elseif (!$this->onCommand($sender, $args)) {
            $sender->sendMessage("$this->prefix$this->usage");
        }
    }

    /**
     * @param CommandSender $sender
     * @param array         $args
     *
     * @return bool
     */
    abstract public function onCommand(CommandSender $sender, array $args) : bool;

    /**
     * @param CommandSender $target
     *
     * @return bool
     */
    public function checkPermission(CommandSender $target) : bool{
        if ($this->permission === null) {
            return true;
        } else {
            return $target->hasPermission($this->permission);
        }
    }

    /** @return string */
    public function getUsage() : string{
        return $this->usage;
    }

    /**
     * @param string $tag
     *
     * @return string
     */
    public function getFullId(string $tag) : string{
        return "$this->strId@$tag";
    }

    /**
     * @param string $label
     *
     * @return bool
     */
    public function checkLabel(string $label) : bool{
        return strcasecmp($label, $this->label) === 0 || $this->aliases && in_arrayi($label, $this->aliases);
    }
}