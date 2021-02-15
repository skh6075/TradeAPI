<?php

namespace nlog\trade\listener;

use nlog\trade\inventory\action\NetworkFakeInventoryAction;
use nlog\trade\inventory\action\NetworkTradeInventoryAction;
use nlog\trade\TradeHandler;
use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\network\mcpe\protocol\types\ContainerIds;
use pocketmine\network\mcpe\protocol\types\NetworkInventoryAction;
use pocketmine\tile\Container;

class TransactionInjector implements Listener{

    public function onDataPacketRecive(DataPacketReceiveEvent $event): void{
        if (($pk = $event->getPacket()) instanceof InventoryTransactionPacket) {
            /** @var InventoryTransactionPacket $pk */
            $res = [];
            $c = false;

            foreach ($pk->actions as $_ => $action) {
                $after = $action;
                if ($action->windowId === ContainerIds::UI and ($action->inventorySlot === 4 or $action->inventorySlot === 5 or $action->inventorySlot === 50)) {
                    if ($action->windowId === 50) {
                        if (TradeHandler::getTradeAPI()->isTrading($event->getPlayer())) {
                            $action->inventorySlot = 51;
                        } else {
                            $res[] = $after;
                            continue;
                        }
                    }
                    $after = new NetworkTradeInventoryAction();
                    $after->inventorySlot = $action->inventorySlot;
                    $after->windowId = $action->windowId;
                    $after->newItem = $action->newItem;
                    $after->oldItem = $action->oldItem;
                    $after->sourceFlags = $action->sourceFlags;
                    $after->sourceType = $action->sourceType;

                    $c = true;
                }
                if ($action->sourceType === NetworkInventoryAction::SOURCE_TODO and ($action->windowId === -31 or $action->windowId === -30)) {
                    $after = new NetworkFakeInventoryAction();
                    $after->inventorySlot = $action->inventorySlot;
                    $after->windowId = $action->windowId;
                    $after->newItem = $action->newItem;
                    $after->oldItem = $action->oldItem;
                    $after->sourceFlags = $action->sourceFlags;
                    $after->sourceType = $action->sourceType;

                    $c = true;
                }

                $res[] = $after;
            }
            if ($c) {
                $pk->actions = $res;
            }
        }
    }
}