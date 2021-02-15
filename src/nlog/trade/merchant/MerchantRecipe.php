<?php

namespace nlog\trade\merchant;

use pocketmine\item\Item;
use pocketmine\nbt\tag\CompoundTag;

class MerchantRecipe{

    private Item $buyA;

    private Item $sell;

    private ?Item $buyB = null;

    private int $maxUses = 999;

    private int $tier = -1;

    private int $buyCountA = -1;

    private int $buyCountB = -1;

    private int $uses = -1;

    private int $rewardExp = -1;

    private int $demand = -1;

    private int $traderExp = -1;

    private float $priceMultiplierA = -1.0;

    private float $priceMultiplierB = -1.0;


    private static function add(CompoundTag $tag, string $k, $v, $minValue = 0): void{
        if (is_int($v)) {
            if ($v > $minValue) {
                $tag->setInt($k, $v);
            }
        }

        if (is_float($v)) {
            if ($v > $minValue) {
                $tag->setInt($k, $v);
            }
        }

        if ($v instanceof Item) {
            $tag->setTag($v->nbtSerialize(-1, $k));
        }
    }

    public function __construct(Item $buyA, Item $sell, ?Item $buyB = null, int $tier = -1, int $buyCountA = -1, int $buyCountB = -1, int $maxUses = 999) {
        $this->buyA = $buyA;
        $this->sell = $sell;

        $this->buyB = $buyB;
        $this->tier = $tier;
        $this->buyCountA = $buyCountA;
        $this->buyCountB = $buyCountB;
        $this->maxUses = $maxUses;
    }

    public function setBuyA(Item $item): void{
        $this->buyA = $item;
    }

    public function setBuyB(?Item $item): void{
        $this->buyB = $item;
    }

    public function setSell(Item $item): void{
        $this->sell = $item;
    }

    public function setTier(int $tier): void{
        $this->tier = $tier;
    }

    public function setBuyCountA(int $buyCountA): void{
        $this->buyCountA = $buyCountA;
    }

    public function setBuyCountB(int $buyCountB): void{
        $this->buyCountB = $buyCountB;
    }

    public function setMaxUses(int $maxUses): void{
        $this->maxUses = $maxUses;
    }

    public function toNBT(): CompoundTag{
        $tag = new CompoundTag();

        self::add($tag, "buyA", $this->buyA);
        self::add($tag, "sell", $this->sell);
        self::add($tag, "buyB", $this->buyB);
        self::add($tag, "tier", $this->tier, -1);
        self::add($tag, "buyCountA", $this->buyCountA);
        self::add($tag, "buyCountB", $this->buyCountB);
        self::add($tag, "uses", max($this->uses, 0), -1);
        self::add($tag, "rewardExp", max($this->rewardExp, 0));
        self::add($tag, "demand", max($this->demand, 0));
        self::add($tag, "traderExp", max($this->traderExp, 0));
        self::add($tag, "priceMultiplierA", max($this->priceMultiplierA, 0.0));
        self::add($tag, "priceMultiplierB", max($this->priceMultiplierB, 0.0));

        return $tag;
    }
}