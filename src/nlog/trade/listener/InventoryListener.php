<?php

namespace nlog\trade\listener;

use nlog\trade\TradeAPI;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\ActorEventPacket;
use pocketmine\network\mcpe\protocol\ContainerClosePacket;

class InventoryListener implements Listener{

    private TradeAPI $trade;

    public function __construct(TradeAPI $trade) {
        $this->trade = $trade;
    }

    /**
     * @param DataPacketReceiveEvent $event
     * @priority MONITOR
     *
     * @ignoreCancelled true
     */
    public function onDataPacketReceive(DataPacketReceiveEvent $event): void{
        $pk = $event->getPacket();

        if ($pk instanceof ContainerClosePacket and $pk->windowId === 255) {
            $this->trade->closeWindow($event->getPlayer(), false);
        } else if ($pk instanceof ActorEventPacket and $pk->event !== ActorEventPacket::COMPLETE_TRADE) {
            $this->trade->doCloseInventory($this->trade->getInventory($event->getPlayer()));
        }
    }

    public function onPlayerCommandPreprocess(PlayerCommandPreprocessEvent $event): void{
        $this->trade->doCloseInventory($this->trade->getInventory($event->getPlayer()));
    }

    public function onBlockBreak(BlockBreakEvent $event): void{
        $this->trade->doCloseInventory($this->trade->getInventory($event->getPlayer()));
    }

    public function onPlayerDeath(PlayerDeathEvent $event): void{
        $this->trade->doCloseInventory($this->trade->getInventory($event->getPlayer()));
    }

    public function onPlayerJoin(PlayerJoinEvent $event): void{
        $this->trade->addInventory($event->getPlayer());
    }

    public function onPlayerQuit(PlayerQuitEvent $event): void{
        $this->trade->doCloseInventory($this->trade->getInventory($event->getPlayer()));
        $this->trade->removeInventory($event->getPlayer());
    }
}