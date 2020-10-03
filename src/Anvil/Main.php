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

use Anvil\EventListener;

//plugin
use pocketmine\plugin\PluginBase;

//event
use pocketmine\event\Listener;

//economyapi
use onebone\economyapi\EconomyAPI;

//utils
use pocketmine\utils\TextFormat;

//formapi
use jojoe77777\FormAPI\SimpleForm;
use jojoe77777\FormAPI\CustomForm;
use jojoe77777\FormAPI\Form;
use jojoe77777\FormAPI\ModalForm;
use jojoe77777\FormAPI\FormAPI;

//item
use pocketmine\item\Item;
use pocketmine\item\Tool;
use pocketmine\item\Armor;

//others
use pocketmine\Server;
use pocketmine\Player;

class Main extends PluginBase implements Listener
{

    public function onEnable(){
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
        
        $this->getLogger()->info(TextFormat::GREEN."CustomAnvilUI Enabled");
        
        @mkdir($this->getDataFolder());
        $this->saveDefaultConfig();
        $this->cfg = $this->getConfig()->getAll();
    }
    
    public function onDisable(){
        $this->getLogger()->info(TextFormat::RED."CustomAnvilUI Disabled!");
    }
    
    public function openForm(Player $player) {


        $form = new SimpleForm(function (Player $player, $data){
            if ($data === null) {
                return;
            }
            switch ($data) {
                case 0:
                    $this->openRepair($player);
                    break;
                case 1:
                    $this->openRename($player);
                    break;


            }
        }
        );
        $form->setTitle($this->getConfig()->getNested("Main.Title"));
        $form->setContent($this->getConfig()->getNested("Main.Content"));
        $form->addButton($this->getConfig()->getNested("Main.Repair-Button"));
        $form->addButton($this->getConfig()->getNested("Main.Rename-Button"));
        $form->addButton($this->getConfig()->getNested("Main.Exit-Button"));
        $form->sendToPlayer($player);
    }

    public function openRepair(Player $player) {


        $form = new SimpleForm(function (Player $player, $data){
            if ($data === null) {
                return;
            }
            switch ($data) {
                case 0:

                    if (\pocketmine\Server::getInstance()->getPluginManager()->getPlugin("EconomyAPI")->myMoney($player) >= $this->getConfig()->getNested("Repair.Cost")) {
                        $item = $player->getInventory()->getItemInHand();
                        if ($item instanceof Armor or $item instanceof Tool) {
                            $id = $item->getId();
                            $meta = $item->getDamage();
                            $player->getInventory()->removeItem(Item::get($id, $meta, 1));
                            $newitem = Item::get($id, 0, 1);
                            if ($item->hasCustomName()) {
                                $newitem->setCustomName($item->getCustomName());
                            }
                            if ($item->hasEnchantments()) {
                                foreach ($item->getEnchantments() as $enchants) {
                                    $newitem->addEnchantment($enchants);
                                }
                            }
                            $player->getInventory()->addItem($newitem);
                            $player->sendMessage($this->getConfig()->getNested("Repair-Message.succesfully"));
                            EconomyAPI::getInstance()->reduceMoney($player, $this->getConfig()->getNested("Repair.Cost"));
                            return true;
                        } else {
                            $player->sendMessage($this->getConfig()->getNested("Repair-Message.HoldItem"));
                            return false;
                        }
                        return true;
                    } else {
                        $player->sendMessage($this->getConfig()->getNested("Repair-Message.NoMoney").$this->getConfig()->getNested("Repair.Cost");
                    }
                    break;
                case 1:
                    $this->openForm($player);
                    break;
            }



        }
        );
        $mymoney1 = EconomyAPI::getInstance()->myMoney($player);
        $form->setTitle($this->getConfig()->getNested("Repair.Title"));
        $form->setContent($this->getConfig()->getNested("Repair.Content")."\n".$this->getConfig()->getNested("Repair.ShowMyMoney"). $mymoney1);
        $form->addButton($this->getConfig()->getNested("Repair.Button"));
        $form->addButton($this->getConfig()->getNested("Repair.Back"));
        $form->sendToPlayer($player);
    }

    public function openRename(Player $sender){
        $form = new CustomForm(function(Player $sender, ?array $data){
            if(!isset($data)) return;
            $item = $sender->getInventory()->getItemInHand();
            if ($item->getId() == 0) {
                $sender->sendMessage($this->getConfig()->getNested("Rename-Message.HoldItem"));
                return;
            }
            if (\pocketmine\Server::getInstance()->getPluginManager()->getPlugin("EconomyAPI")->myMoney($sender) >= $this->getConfig()->getNested("Rename.Cost")) {
                EconomyAPI::getInstance()->reduceMoney($sender, $this->getConfig()->getNested("Rename.Cost"));
                $item->setCustomName($data[1]);
                $sender->getInventory()->setItemInHand($item);
                $sender->sendMessage($this->getConfig()->getNested("Rename-Message.succesfully")." ".$data[1]);
            } else {
                $sender->sendMessage($this->getConfig()->getNested("Rename-Message.NoMoney")." ".$this->getConfig()->getNested("Rename.Cost"));
            }


        });
        $mymoney = EconomyAPI::getInstance()->myMoney($sender);
        $form->setTitle($this->getConfig()->getNested("Rename.Title").$sender->getInventory()->getItemInHand()->getName());
        $form->addLabel($this->getConfig()->getNested("Rename.ShowMyMoney").$mymoney."\n".$this->getConfig()->getNested("Rename.Content").$this->getConfig()->getNested("Rename.Cost"));
        $form->addInput(TextFormat::GOLD."Name:", "§a§lNamen von item hierhin");
        $form->sendToPlayer($sender);
    }
}
