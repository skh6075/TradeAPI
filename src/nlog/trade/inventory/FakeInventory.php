<?php

namespace nlog\trade\inventory;

use pocketmine\inventory\BaseInventory;
use pocketmine\item\Item;

class FakeInventory extends BaseInventory{

    public function __construct(Item $item) {
        parent::__construct([], 1);
        $this->setItem(0, $item);
    }

    public function getDefaultSize(): int{
        return 1;
    }

    public function getName(): string{
        return "FakeInventory";
    }
}