<?php

namespace presentkim\gaitspeed\listener;

use pocketmine\entity\Attribute;
use pocketmine\event\{
  Listener, player\PlayerRespawnEvent
};
use presentkim\gaitspeed\GaitSpeedMain as Plugin;

class PlayerEventListener implements Listener{

    /** @var Plugin */
    private $owner = null;

    public function __construct(){
        $this->owner = Plugin::getInstance();
    }

    /** @param PlayerRespawnEvent $event */
    public function onPlayerRespawnEvent(PlayerRespawnEvent $event) : void{
        $player = $event->getPlayer();
        $result = $this->owner->query('SELECT gait_speed FROM gait_speed_list WHERE player_name = ' . strtolower($player->getName()) . ';')->fetchArray(SQLITE3_NUM)[0];
        if ($result !== null) { // When query result is exists
            $speed = ((int) $result) * 0.001;
        } else {
            $speed = ((int) $this->owner->getConfig()->get("default-speed")) * 0.001;
        }
        $player->getAttributeMap()->getAttribute(Attribute::MOVEMENT_SPEED)->setValue($speed);
    }
}