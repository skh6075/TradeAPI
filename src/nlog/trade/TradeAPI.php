<?php

namespace nlog\trade;

use nlog\trade\inventory\PlayerTradeInventory;
use nlog\trade\merchant\MerchantRecipeList;
use nlog\trade\merchant\TradeProperties;
use pocketmine\entity\Entity;
use pocketmine\entity\EntityIds;
use pocketmine\nbt\NetworkLittleEndianNBTStream;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\network\mcpe\protocol\AddActorPacket;
use pocketmine\network\mcpe\protocol\ContainerClosePacket;
use pocketmine\network\mcpe\protocol\RemoveActorPacket;
use pocketmine\network\mcpe\protocol\types\WindowTypes;
use pocketmine\network\mcpe\protocol\UpdateTradePacket;
use pocketmine\Player;

final class TradeAPI{

    /** @var PlayerTradeInventory[] */
    private static array $inventory = [];

    private array $process = [];


    public function __construct() {
    }

    public static function addInventory(Player $player): void{
        self::$inventory[$player->getName()] = new PlayerTradeInventory($player);
    }

    public static function removeInventory(Player $player): void{
        if (isset(self::$inventory[$player->getName()])) {
            unset(self::$inventory[$player->getName()]);
        }
    }

    public static function getInventory(Player $player): ?PlayerTradeInventory{
        return self::$inventory[$player->getName()] ?? null;
    }

    public function isTrading(Player $player): bool{
        return isset($this->process[$player->getName()]);
    }

    public function closeWindow(Player $player, bool $sendPacket = true): void{
        /** @var TradeProperties $prop */
        if (($prop = $this->process[$player->getName()] ?? null) instanceof TradeProperties) {
            if ($sendPacket) {
                $pk = new ContainerClosePacket();
                $pk->windowId = WindowTypes::TRADING;
                $player->sendDataPacket($pk);
            }

            if ($prop->entity instanceof Entity) {
                $prop->entity->getDataPropertyManager()->setInt(Entity::DATA_TRADING_PLAYER_EID, -1);
            } else {
                $pk = new RemoveActorPacket();
                $pk->entityUniqueId = $prop->eid;
                $player->sendDataPacket($pk);
            }

            $this->doCloseInventory(self::getInventory($player));
            unset($this->process[$player->getName()]);
        }
    }

    public function doCloseInventory(PlayerTradeInventory $inventory): void{
        for ($slot = 0; $slot <= 1; $slot++) {
            $item = $inventory->getItem($slot);

            if ($inventory->getHolder()->getInventory()->canAddItem($item)) {
                $inventory->getHolder()->getInventory()->addItem($item);
            } else {
                $inventory->getHolder()->dropItem($item);
            }
        }

        $inventory->clearAll();
    }

    public function sendWindow(Player $player, MerchantRecipeList $recipeList, TradeProperties $properties): void{
        $this->closeWindow($player);

        $pk = new UpdateTradePacket();
        $pk->windowId = WindowTypes::TRADING;
        $pk->displayName = $properties->tradeName;
        $pk->isV2Trading = true;
        $pk->isWilling = true;
        $pk->tradeTier = $properties->tradeTier;
        $pk->playerEid = $player->getId();
        $pk->offers = (new NetworkLittleEndianNBTStream())->write(new CompoundTag("", [
            new ListTag("TierExpRequirements", [
                new CompoundTag("", [new IntTag("0", 0)]),
                new CompoundTag("", [new IntTag("1", 10)]),
                new CompoundTag("", [new IntTag("2", 60)]),
                new CompoundTag("", [new IntTag("3", 160)]),
                new CompoundTag("", [new IntTag("4", 310)]),
            ])
        ]));
        $metadata = [
            Entity::DATA_TRADE_TIER => [Entity::DATA_TYPE_INT, $pk->tradeTier],
            Entity::DATA_TRADE_XP => [Entity::DATA_TYPE_INT, $properties->xp],
            Entity::DATA_MAX_TRADE_TIER => [Entity::DATA_TYPE_INT, $properties->maxTradeTier],
            Entity::DATA_TRADING_PLAYER_EID => [Entity::DATA_TYPE_INT, $player->getId()]
        ];

        if ($properties->entity instanceof Entity) {
            $pk->traderEid = $properties->entity->getId();

            foreach ($metadata as $k => $metadataProperty) {
                $properties->entity->getDataPropertyManager()->setInt($k, $metadataProperty[1]);
            }
        } else {
            $apk = new AddActorPacket();
            $apk->type = AddActorPacket::LEGACY_ID_MAP_BC[EntityIds::NPC];
            $apk->position = $player->getPosition()->add(0, -2);
            $apk->metadata = $metadata;

            $properties->eid = $apk->entityRuntimeId = $pk->traderEid = Entity::$entityCount++;

            $player->sendDataPacket($apk);
        }
        $this->process[$player->getName()] = clone  $properties;
        $player->sendDataPacket($pk);
    }
}