<?php

declare(strict_types=1);

namespace pocketmine\event {
	interface Listener {}
}

namespace pocketmine\network\mcpe {
	final class JwtException extends \RuntimeException {}
	final class JwtUtils {
		/** @return array{array<mixed>, array<mixed>, string} */
		public static function parse(string $token): array {
			if ($token === 'invalid') {
				throw new JwtException('Invalid JWT');
			}
			$claims = json_decode($token, true, 512, JSON_THROW_ON_ERROR);
			return [[], $claims, ''];
		}
	}
	final class NetworkSession {
		/** @var list<string> */
		public array $disconnects = [];
		public function disconnect(string $message): void { $this->disconnects[] = $message; }
	}
}

namespace pocketmine\network\mcpe\protocol {
	final class LoginPacket {
		public function __construct(public string $clientDataJwt) {}
	}
}

namespace pocketmine\network\mcpe\protocol\types {
	final class DeviceOS {
		public const ANDROID = 1;
	}
}

namespace pocketmine\event\server {
	final class DataPacketReceiveEvent {
		public function __construct(private object $packet, private \pocketmine\network\mcpe\NetworkSession $origin) {}
		public function getPacket(): object { return $this->packet; }
		public function getOrigin(): \pocketmine\network\mcpe\NetworkSession { return $this->origin; }
	}
}

namespace pocketmine\utils {
	final class Config {
		public function __construct(private mixed $message) {}
		public function get(string $key): mixed { return $this->message; }
	}
}

namespace pocketmine\plugin {
	class PluginBase {
		public static mixed $configuredMessage = 'Use original Minecraft.';
		public function getConfig(): \pocketmine\utils\Config { return new \pocketmine\utils\Config(self::$configuredMessage); }
		public function saveDefaultConfig(): void {}
		public function getServer(): object { return new class {
			public function getPluginManager(): object { return new class {
				public function registerEvents(object $listener, object $plugin): void {}
			}; }
		}; }
	}
}

namespace {
	require __DIR__ . '/../src/NgLamVN/AntiToolBox/Loader.php';

	use NgLamVN\AntiToolBox\Loader;
	use pocketmine\event\server\DataPacketReceiveEvent;
	use pocketmine\network\mcpe\NetworkSession;
	use pocketmine\network\mcpe\protocol\LoginPacket;
	use pocketmine\plugin\PluginBase;

	function check(bool $condition, string $message): void {
		if (!$condition) { throw new \RuntimeException($message); }
	}

	$plugin = new Loader();
	$session = new NetworkSession();
	foreach ([
		new \stdClass(),
		new LoginPacket('invalid'),
		new LoginPacket('{"DeviceOS":2,"DeviceModel":"Xiaomi Note 8"}'),
		new LoginPacket('{"DeviceOS":1,"DeviceModel":3}'),
		new LoginPacket('{"DeviceOS":1,"DeviceModel":""}'),
		new LoginPacket('{"DeviceOS":1,"DeviceModel":"XIAOMI Note 8"}'),
	] as $packet) {
		$plugin->onReceive(new DataPacketReceiveEvent($packet, $session));
		check($session->disconnects === [], 'Non-matching or malformed login disconnected');
	}
	$plugin->onReceive(new DataPacketReceiveEvent(new LoginPacket('{"DeviceOS":1,"DeviceModel":"Xiaomi Note 8"}'), $session));
	check($session->disconnects === ['Use original Minecraft.'], 'Case mismatch was not flagged');
	PluginBase::$configuredMessage = false;
	$session = new NetworkSession();
	$plugin->onReceive(new DataPacketReceiveEvent(new LoginPacket('{"DeviceOS":1,"DeviceModel":"samsung SM-A105F"}'), $session));
	check($session->disconnects === ['Please use the original client to join.'], 'Invalid message fallback failed');
	echo "PASS: malformed login, Android model check and message fallback\n";
}
