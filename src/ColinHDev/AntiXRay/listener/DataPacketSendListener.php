<?php

namespace ColinHDev\AntiXRay\listener;

use ColinHDev\AntiXRay\ResourceManager;
use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\UpdateBlockPacket;

class DataPacketSendListener implements Listener {

    public function onDataPacketSend(DataPacketSendEvent $event) : void {
        $standard = ResourceManager::getInstance()->getAntiXRayStandard();
        $worlds = ResourceManager::getInstance()->getWorlds();
        $packets = $event->getPackets();
        foreach ($packets as $packet) {
            if (!$packet instanceof UpdateBlockPacket) continue;
            foreach ($this->getAllSidesOfVector(new Vector3($packet->blockPosition->getX(), $packet->blockPosition->getY(), $packet->blockPosition->getZ())) as $vector) {
                $vectors = array_merge([$vector], $this->getAllSidesOfVector($vector));
                foreach ($event->getTargets() as $target) {
                    $world = $target->getPlayer()->getWorld();
                    if (
                        ($standard && in_array($world->getFolderName(), $worlds, true))
                        ||
                        (!$standard && !in_array($world->getFolderName(), $worlds, true))
                    ) {
                        continue;
                    }
                    foreach ($world->createBlockUpdatePackets($vectors) as $updateBlockPacket) {
                        $target->addToSendBuffer($updateBlockPacket);
                    }
                }
            }
        }
    }

    /**
     * @return Vector3[]
     */
    private function getAllSidesOfVector(Vector3 $vector3) : array {
        $floorX = $vector3->getFloorX();
        $floorY = $vector3->getFloorY();
        $floorZ = $vector3->getFloorZ();
        return [
            new Vector3($floorX + 1, $floorY, $floorZ),
            new Vector3($floorX - 1, $floorY, $floorZ),
            new Vector3($floorX, $floorY + 1, $floorZ),
            new Vector3($floorX, $floorY - 1, $floorZ),
            new Vector3($floorX, $floorY, $floorZ + 1),
            new Vector3($floorX, $floorY, $floorZ - 1)
        ];
    }
}