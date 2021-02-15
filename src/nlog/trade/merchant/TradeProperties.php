<?php

namespace nlog\trade\merchant;

use pocketmine\entity\Entity;

class TradeProperties{

    public ?Entity $entity = null;

    public string $tradeName;

    public int $xp = 0;

    public int $tradeTier;

    public int $maxTradeTier;

    public int $eid;
}