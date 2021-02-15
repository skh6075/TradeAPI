<?php

namespace nlog\trade;

use nlog\trade\listener\InventoryListener;
use nlog\trade\listener\TransactionInjector;
use pocketmine\plugin\Plugin;

final class TradeHandler{

    private static ?Plugin $registrant = null;

    private static TradeAPI $trade;


    public static function getRegistrant(): ?Plugin{
        return self::$registrant;
    }

    public static function isRegistrant(): bool{
        return self::$registrant instanceof Plugin;
    }

    public static function register(Plugin $plugin): void{
        if (self::isRegistrant()) {
            throw new \InvalidArgumentException("{$plugin->getName()} attempted to register " . self::class . "twice.");
        }

        self::$registrant = $plugin;
        self::$trade = new TradeAPI();

        $plugin->getServer()->getPluginManager()->registerEvents(new InventoryListener(self::$trade), $plugin);
        $plugin->getServer()->getPluginManager()->registerEvents(new TransactionInjector(), $plugin);
    }

    public static function getTradeAPI(): TradeAPI{
        return self::$trade;
    }
}