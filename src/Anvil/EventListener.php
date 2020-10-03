<?php

/*
  ____        ____            _    _
 |  _ \  __ _|  _ \ ___  __ _| |  / \   __ _ _   _  __ _
 | | | |/ _` | |_) / _ \/ _` | | / _ \ / _` | | | |/ _` |
 | |_| | (_| |  _ <  __/ (_| | |/ ___ \ (_| | |_| | (_| |
 |____/ \__,_|_| \_\___|\__,_|_/_/   \_\__, |\__,_|\__,_|
                                           |_|
*/
declare(strict_types=1);

namespace Anvil;

use Anvil\Main;

//event
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\Listener;

//item
use pocketmine\Item;

//block
use pocketmine\block\Anvil;

Class EventListener implements Listener{

    /** @var Main */
    private $plugin;

    public function __construct(Main $plugin){
        $this->plugin = $plugin;
    }

    /**
     * @param PlayerInteractEvent $ev
     */
    public function onInteract(PlayerInteractEvent $event){
        if($event->getAction() !== PlayerInteractEvent::RIGHT_CLICK_BLOCK) return;
        if($event->getAction() !== PlayerInteractEvent::LEFT_CLICK_BLOCK) return;
        if($event->getBlock() instanceof Anvil){
            $event->setCancelled();
            $this->plugin->openForm($event->getPlayer());
        }
    }
}
