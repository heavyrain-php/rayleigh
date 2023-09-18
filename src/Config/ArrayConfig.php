<?php declare(strict_types=1);

/**
 * @license MIT
 */

namespace Rayleigh\Config;

use Rayleigh\Contracts\Config;

final readonly class ArrayConfig implements Config
{
    public function __construct(
        private array $config,
    ) {
    }

    public function getString(string $key): string
    {
        $value = $this->getValue($key);
        if (!\is_string($value)) {
            throw new \InvalidArgumentException('Invalid config value provided key=' . $key . ' value=' . $value);
        }
        return \strval($value);
    }

    public function getStringArray(string $key): array
    {
        $value = $this->getValue($key);
        $valuesRaw = \explode(',', $value);
        $values = [];
        foreach ($valuesRaw as $v) {
            if (!\is_string($v)) {
                throw new \InvalidArgumentException('Invalid config value provided key=' . $key . ' value=' . $v);
            }
            $values[] = \strval($v);
        }
        return $values;
    }

    public function getInteger(string $key): int
    {
        $value = $this->getValue($key);
        if (false === \preg_match('/^[0-9]+$/', $value)) {
            throw new \InvalidArgumentException('Invalid config value provided key=' . $key . ' value=' . $value);
        }
        return \intval($value);
    }

    public function getIntegerArray(string $key): array
    {
        $value = $this->getValue($key);
        $valuesRaw = \explode(',', $value);
        $values = [];
        foreach ($valuesRaw as $v) {
            if (false === \preg_match('/^[0-9]+$/', $v)) {
                throw new \InvalidArgumentException('Invalid config value provided key=' . $key . ' value=' . $v);
            }
            $values[] = \intval($v);
        }
        return $values;
    }

    public function getBoolean(string $key): bool
    {
        $value = $this->getValue($key);
        if ($value !== 'true' && $value !== 'false') {
            throw new \InvalidArgumentException('Invalid config value provided key=' . $key . ' value=' . $value);
        }
        return \boolval($value);
    }

    private function getValue(string $key): mixed
    {
        if (!\array_key_exists($key, $this->config)) {
            throw new \InvalidArgumentException('Undefined config key provided key=' . $key);
        }
        return $this->config[$key];
    }
}
