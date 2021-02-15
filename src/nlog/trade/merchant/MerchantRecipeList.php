<?php

namespace nlog\trade\merchant;

use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;

class MerchantRecipeList{

    /** @var MerchantRecipe[] */
    private array $recipes = [];

    private bool $changed = true;

    private ListTag $nbt;


    public function __construct(MerchantRecipe ...$recipes){
        foreach ($recipes as $recipe) {
            $this->push($recipe);
        }
        $this->nbt = new ListTag();
    }

    public function push(MerchantRecipe $recipe): int{
        $this->recipes[] = clone $recipe;
        $this->changed = true;

        return count($this->recipes) - 1;
    }

    public function pop(): ?MerchantRecipe{
        if (count($this->recipes)) {
            $this->changed = true;
            return array_pop($this->recipes);
        }
        return null;
    }

    public function toNBT(bool $cache = true): ListTag{
        if (!$cache or $this->changed) {
            $this->nbt = new ListTag("Recipes", array_map(function (MerchantRecipe $recipe): CompoundTag {
                return $recipe->toNBT();
            }, $this->recipes));

            $this->changed = false;
        }
        return $this->nbt;
    }
}