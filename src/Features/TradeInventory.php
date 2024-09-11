<?php

namespace PremierMcpe\TradeGui\Features;

use muqsit\invmenu\InvMenu;
use muqsit\invmenu\transaction\InvMenuTransaction;
use muqsit\invmenu\transaction\InvMenuTransactionResult;
use muqsit\invmenu\type\InvMenuTypeIds;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\item\StringToItemParser;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\player\Player;
use pocketmine\scheduler\TaskHandler;
use PremierMcpe\TradeGui\Enums\ActionEnum;
use PremierMcpe\TradeGui\Enums\TimerStateEnum;
use PremierMcpe\TradeGui\Events\TradeCompleteEvent;
use PremierMcpe\TradeGui\Main;
use PremierMcpe\TradeGui\Tasks\TradeCompleteTask;
use PremierMcpe\TradeGui\Tasks\TradeEndTask;
use PremierMcpe\TradeGui\Tasks\TradeStartTask;
use PremierMcpe\TradeGui\Utils\InventoryTitle;

class TradeInventory
{
    private const SENDER_SLOTS = [0, 1, 2, 3, 9, 10, 11, 12, 18, 19, 20, 21, 27, 28, 29, 30, 36, 37, 38, 39, 45, 46, 47, 48];
    private const RECEIVER_SLOTS = [5, 6, 7, 8, 14, 15, 16, 17, 23, 24, 25, 26, 32, 33, 34, 35, 41, 42, 43, 44, 50, 51, 52, 53];
    private const TIMER_SLOTS = [4, 13, 22, 31, 40, 49];
    private const SENDER_ACTION_SLOT = 48;
    private const RECEIVER_ACTION_SLOT = 50;
    private const ACTION_KEY = 'action';
    private const NAME_WAITING_TRADE = 'Waiting for start trade';

    private InvMenu $inventory;
    private ?TaskHandler $startTask = null;
    private TimerStateEnum $state = TimerStateEnum::HOLD;

    public function __construct(
        private readonly Player $sender,
        private readonly Player $receiver
    )
    {
        $title = InventoryTitle::format($this->sender->getName(), $this->receiver->getName());
        $this->inventory = InvMenu::create(InvMenuTypeIds::TYPE_DOUBLE_CHEST)->setName($title);
        $this->inventory->setListener(fn(InvMenuTransaction $transaction) => $this->handleInventoryInteraction($transaction));
        $this->inventory->setInventoryCloseListener(fn(Player $player, Inventory $inventory) => $this->handleInventoryClose($player, $inventory));
        $this->setupInitialItems();
    }

    private function handleInventoryInteraction(InvMenuTransaction $transaction): InvMenuTransactionResult
    {
        $player = $transaction->getPlayer();
        $slot = $transaction->getAction()->getSlot();
        $item = $transaction->getItemClicked();

        if ($this->state === TimerStateEnum::DONE) {
            return $transaction->discard();
        }

        if ($this->state === TimerStateEnum::IN_PROGRESS) {
            if ($this->isActionSlotForPlayer($player, $slot)) {
                $this->resetTradeTimer();
            }
            return $transaction->discard();
        }

        if ($this->isActionSlotForPlayer($player, $slot)) {
            $this->toggleApprovalState($slot, $item);
            if ($this->areBothPlayersApproved()) {
                $this->startTradeTimer();
            }
            return $transaction->discard();
        }

        return $this->isValidSlot($player, $slot) ? $transaction->continue() : $transaction->discard();
    }

    private function isActionSlotForPlayer(Player $player, int $slot): bool
    {
        return ($this->isSender($player) && $slot === self::SENDER_ACTION_SLOT)
            || ($this->isReceiver($player) && $slot === self::RECEIVER_ACTION_SLOT);
    }

    private function isSender(Player $player): bool
    {
        return $player->getId() === $this->sender->getId();
    }

    private function isReceiver(Player $player): bool
    {
        return $player->getId() === $this->receiver->getId();
    }

    private function resetTradeTimer(): void
    {
        if ($this->startTask !== null) {
            $this->startTask->cancel();
            $this->startTask = null;
        }
        $this->setState(TimerStateEnum::HOLD);
        $this->setupInitialItems();
    }

    private function setState(TimerStateEnum $newState): void
    {
        $this->state = $newState;
    }

    private function setupInitialItems(): void
    {
        $localization = Main::getInstance()->getLocalization($this->sender->getLocale());
        $glassItem = $this->createTimerItem($this->state->getBlockType(), $localization->translate(TradeTranslations::WAITING_FOR_START_TRADE));

        foreach (self::TIMER_SLOTS as $timerSlot) {
            $this->setItemInSlot($timerSlot, $glassItem);
        }

        $this->setItemInSlot(self::SENDER_ACTION_SLOT, $this->createActionItem(ActionEnum::APPROVE));
        $this->setItemInSlot(self::RECEIVER_ACTION_SLOT, $this->createActionItem(ActionEnum::APPROVE));
    }

    private function createTimerItem(string $blockType, string $customName): Item
    {
        $item = StringToItemParser::getInstance()->parse($blockType);
        $item->setCustomName($customName);
        return $item;
    }

    private function setItemInSlot(int $slot, Item $item): void
    {
        $this->inventory->getInventory()->setItem($slot, $item);
    }

    private function createActionItem(ActionEnum $action): Item
    {
        $localization = Main::getInstance()->getLocalization($this->sender->getLocale());
        $item = StringToItemParser::getInstance()->parse($action->getItemType());
        $item->setNamedTag($this->createActionCompoundTag($action));
        $item->setCustomName($localization->translate($action->getItemName()));
        return $item;
    }

    private function createActionCompoundTag(ActionEnum $action): CompoundTag
    {
        return CompoundTag::create()->setString(self::ACTION_KEY, $action->value);
    }

    private function toggleApprovalState(int $slot, Item $item): void
    {
        $currentAction = ActionEnum::tryFrom($item->getNamedTag()->getString(self::ACTION_KEY)) ?? ActionEnum::APPROVE;
        $newAction = $currentAction === ActionEnum::APPROVE ? ActionEnum::APPROVED : ActionEnum::APPROVE;

        $this->setItemInSlot($slot, $this->createActionItem($newAction));
    }

    private function areBothPlayersApproved(): bool
    {
        return $this->getActionFromSlot(self::SENDER_ACTION_SLOT) === ActionEnum::APPROVED &&
            $this->getActionFromSlot(self::RECEIVER_ACTION_SLOT) === ActionEnum::APPROVED;
    }

    private function getActionFromSlot(int $slot): ?ActionEnum
    {
        $item = $this->inventory->getInventory()->getItem($slot);
        return ActionEnum::tryFrom($item->getNamedTag()->getString(self::ACTION_KEY));
    }

    private function startTradeTimer(): void
    {
        $this->setState(TimerStateEnum::IN_PROGRESS);
        $this->startTask = Main::getInstance()->getScheduler()->scheduleRepeatingTask(new TradeStartTask($this, 5), 20);
    }

    private function isValidSlot(Player $player, int $slot): bool
    {
        $allowedSlots = $this->getAllowedSlotsForPlayer($player);
        $actionSlot = $this->getPlayerActionSlot($player);

        return in_array($slot, $allowedSlots, true) && $slot !== $actionSlot;
    }

    private function getAllowedSlotsForPlayer(Player $player): array
    {
        return $this->isSender($player) ? self::SENDER_SLOTS : self::RECEIVER_SLOTS;
    }

    private function getPlayerActionSlot(Player $player): int
    {
        return $this->isSender($player) ? self::SENDER_ACTION_SLOT : self::RECEIVER_ACTION_SLOT;
    }

    public function handleInventoryClose(Player $player, Inventory $inventory): void
    {
        $this->close();

        $items = $inventory->getContents();

        $slots = $player->getId() === $this->sender->getId() ? self::SENDER_SLOTS : self::RECEIVER_SLOTS;

        foreach ($slots as $slot) {
            if ($slot === self::SENDER_ACTION_SLOT || $slot === self::RECEIVER_ACTION_SLOT) {
                continue;
            }

            if (isset($items[$slot])) {
                $player->getInventory()->addItem($items[$slot]);
            }
        }

        $event = new TradeCompleteEvent($this->sender, $this->receiver);
        $event->call();
    }

    public function close(): void
    {
        $this->sender->removeCurrentWindow();
        $this->receiver->removeCurrentWindow();
    }

    public function open(): void
    {
        $this->inventory->send($this->sender);
        $this->inventory->send($this->receiver);
    }

    public function setTimerSecond(int $timerSecond): void
    {
        $localization = Main::getInstance()->getLocalization($this->sender->getLocale());
        $leftSecond = abs(5 - $timerSecond);
        $slot = self::TIMER_SLOTS[$leftSecond];
        $item = $this->createTimerItem($this->state->getBlockType(), $localization->translate(TradeTranslations::LEFT_N_SECONDS, ['second' => $timerSecond]));

        $this->setItemInSlot($slot, $item);
    }

    public function endTrade(): void
    {
        $localization = Main::getInstance()->getLocalization($this->sender->getLocale());
        $this->setState(TimerStateEnum::DONE);
        $item = $this->createTimerItem($this->state->getBlockType(), $localization->translate(TradeTranslations::TRADE_COMPLETED));
        $item->setLore([$localization->translate(TradeTranslations::WINDOW_WILL_BE_CLOSE)]);

        foreach (self::TIMER_SLOTS as $timerSlot) {
            $this->setItemInSlot($timerSlot, $item);
        }

        Main::getInstance()->getScheduler()->scheduleDelayedTask(new TradeEndTask($this), 20);
    }

    public function completeTrade(): void
    {
        $inventory = $this->inventory->getInventory();

        foreach (self::SENDER_SLOTS as $index => $senderSlot) {
            $receiverSlot = self::RECEIVER_SLOTS[$index];

            if ($senderSlot === self::SENDER_ACTION_SLOT || $receiverSlot === self::RECEIVER_ACTION_SLOT) {
                continue;
            }

            $senderItem = $inventory->getItem($senderSlot);
            $receiverItem = $inventory->getItem($receiverSlot);

            $inventory->setItem($senderSlot, $receiverItem);
            $inventory->setItem($receiverSlot, $senderItem);
        }

        Main::getInstance()->getScheduler()->scheduleDelayedTask(new TradeCompleteTask($this), 20);
    }
}
