<?php

declare(strict_types=1);

namespace NgLamVN\AntiToolBox;

use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\JwtException;
use pocketmine\network\mcpe\JwtUtils;
use pocketmine\network\mcpe\protocol\LoginPacket;
use pocketmine\network\mcpe\protocol\types\DeviceOS;
use pocketmine\plugin\PluginBase;

class Loader extends PluginBase implements Listener {

	protected function onEnable(): void {
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->saveDefaultConfig();
	}

	/**
	 * @param DataPacketReceiveEvent $event
	 * @priority NORMAL
	 * @ignoreCancelled TRUE
	 */
	public function onReceive(DataPacketReceiveEvent $event): void {
		$packet = $event->getPacket();
		if (!$packet instanceof LoginPacket) {
			return;
		}
		try {
			[, $claims] = JwtUtils::parse($packet->clientDataJwt);
		} catch (JwtException) {
			return;
		}
		$deviceOS = $claims['DeviceOS'] ?? null;
		$deviceModel = $claims['DeviceModel'] ?? null;
		if ($deviceOS !== DeviceOS::ANDROID || !is_string($deviceModel)) {
			return;
		}
		$words = preg_split('/\s+/', trim($deviceModel), 2);
		$brand = $words[0] ?? '';
		if ($brand !== '' && strtoupper($brand) !== $brand) {
			$message = $this->getConfig()->get('kickMessage');
			$event->getOrigin()->disconnect(is_string($message) ? $message : 'Please use the original client to join.');
		}
	}
}
