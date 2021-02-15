<?php

namespace nlog\trade\inventory\action;

use nlog\trade\TradeAPI;
use pocketmine\inventory\transaction\action\InventoryAction;
use pocketmine\inventory\transaction\action\SlotChangeAction;
use pocketmine\network\mcpe\protocol\types\ContainerIds;
use pocketmine\network\mcpe\protocol\types\NetworkInventoryAction;
use pocketmine\Player;

class NetworkTradeInventoryAction extends NetworkInventoryAction{

    public function createInventoryAction(Player $player) : ?InventoryAction{
        if($this->oldItem->equalsExact($this->newItem)){
            return null;
        }
        switch($this->sourceType){
            case self::SOURCE_CONTAINER:
                $window = null;
                $slot = $this->inventorySlot;
                if($this->windowId === ContainerIds::UI and ($slot === 4 || $slot === 5)){
                    $window = TradeAPI::getInventory($player);
                    $slot -= 4;
                }
                if($this->windowId === ContainerIds::UI and $slot === 51){
                    $window = TradeAPI::getInventory($player);
                    $slot -= 49;
                }
                if($window !== null){
                    return new SlotChangeAction($window, $slot, $this->oldItem, $this->newItem);
                }

                throw new \UnexpectedValueException("No open container with window ID $this->windowId");
            default:
                throw new \UnexpectedValueException("Unknown inventory source type $this->sourceType");
        }
    }
}