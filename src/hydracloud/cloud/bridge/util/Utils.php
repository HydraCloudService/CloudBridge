<?php

namespace hydracloud\cloud\bridge\util;

use InvalidArgumentException;
use pocketmine\entity\Location;
use pocketmine\math\Vector3;
use pocketmine\Server;
use pocketmine\world\Position;
use RuntimeException;

final class Utils {

    public static function containKeys(array $array, ...$keys): bool {
        foreach ($keys as $key) {
            if (!array_key_exists($key, $array)) return false;
        }
        return true;
    }

    public static function convertToString(Position|Location|Vector3 $vector): string {
        return match ($vector::class) {
            Location::class => $vector->getX() . ":" . $vector->getY() . ":" . $vector->getZ() . ":" . $vector->getWorld()->getFolderName() . ":" . $vector->getYaw() . ":" . $vector->getPitch(),
            Position::class => $vector->getX() . ":" . $vector->getY() . ":" . $vector->getZ() . ":" . $vector->getWorld()->getFolderName(),
            default => $vector->getX() . ":" . $vector->getY() . ":" . $vector->getZ()
        };
    }

    public static function convertToVector(string $string): Vector3 {
        $explode = explode(":", $string);
        var_dump(count($explode));
        return match (count($explode)) {
            6 => new Location(floatval($explode[0]), floatval($explode[1]), floatval($explode[2]), Server::getInstance()->getWorldManager()->getWorldByName($explode[3]), floatval($explode[4]), floatval($explode[5])),
            4 => new Position(floatval($explode[0]), floatval($explode[1]), floatval($explode[2]), Server::getInstance()->getWorldManager()->getWorldByName($explode[3])),
            3 => new Vector3(floatval($explode[0]), floatval($explode[1]), floatval($explode[2])),
            default => Vector3::zero()
        };
    }

    public static function fromImage($pathOrImage): string {
        if (is_string($pathOrImage)) {
            if (!is_file($pathOrImage)) {
                throw new InvalidArgumentException("Image path does not exist: $pathOrImage");
            }

            $image = imagecreatefrompng($pathOrImage);
            if (!$image) {
                throw new RuntimeException("Failed to load image from: $pathOrImage");
            }
        } elseif (is_resource($pathOrImage) || $pathOrImage instanceof \GdImage) {
            $image = $pathOrImage;
        } else {
            throw new InvalidArgumentException("Expected string path or GD image resource");
        }

        $width = imagesx($image);
        $height = imagesy($image);
        $bytes = '';

        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                $rgba = imagecolorat($image, $x, $y);
                $a = ((~($rgba >> 24)) << 1) & 0xFF;
                $r = ($rgba >> 16) & 0xFF;
                $g = ($rgba >> 8) & 0xFF;
                $b = $rgba & 0xFF;

                $bytes .= chr($r) . chr($g) . chr($b) . chr($a);
            }
        }

        if (is_string($pathOrImage)) {
            imagedestroy($image);
        }

        return $bytes;
    }
}