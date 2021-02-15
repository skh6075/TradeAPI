<?php

namespace nlog\trade\inventory;

use pocketmine\inventory\BaseInventory;
use pocketmine\Player;

class PlayerTradeInventory extends BaseInventory{

    protected Player $holder;


    public function __construct(Player $holder) {
        $this->holder = $holder;
        parent::__construct([], 3);
    }

    public function getHolder(): Player{
        return $this->holder;
    }

    public function getName(): string{
        return "PlayerTradeInventory";
    }

    public function getDefaultSize(): int{
        return 3;
    }
}