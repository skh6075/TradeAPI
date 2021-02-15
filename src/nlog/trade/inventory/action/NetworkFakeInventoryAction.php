<?php

namespace nlog\trade\inventory\action;

use nlog\trade\inventory\FakeInventory;
use pocketmine\inventory\transaction\action\InventoryAction;
use pocketmine\inventory\transaction\action\SlotChangeAction;
use pocketmine\network\mcpe\protocol\types\NetworkInventoryAction;
use pocketmine\Player;

class NetworkFakeInventoryAction extends NetworkInventoryAction{

    public function createInventoryAction(Player $player): ?InventoryAction{
        if ($this->oldItem->equalsExact($this->newItem)) {
            return null;
        }
        switch ($this->sourceType) {
            case self::SOURCE_TODO:
                if ($this->windowId === -30) {
                    return new SlotChangeAction(new FakeInventory($this->oldItem), $this->inventorySlot, $this->oldItem, $this->newItem);
                }
                if ($this->windowId === -31) {
                    return new SlotChangeAction(new FakeInventory($this->oldItem), $this->inventorySlot, $this->oldItem, $this->newItem);
                }
                throw new \UnexpectedValueException("No open container with window ID $this->windowId");
            default:
                throw new \UnexpectedValueException("Unknown inventory source type $this->sourceType");
        }
    }
}