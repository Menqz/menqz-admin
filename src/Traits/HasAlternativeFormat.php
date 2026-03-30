<?php

namespace MenqzAdmin\Admin\Traits;

trait HasAlternativeFormat
{
    protected static $alternativeDateFormat = null;

    protected static $alternativeDatetimeFormat = null;

    protected static $alternativeTimeFormat = null;

    public static function setAlternativeDateFormat(?string $format): void
    {
        static::$alternativeDateFormat = $format;
    }

    public static function setAlternativeDatetimeFormat(?string $format): void
    {
        static::$alternativeDatetimeFormat = $format;
    }

    public static function setAlternativeTimeFormat(?string $format): void
    {
        static::$alternativeTimeFormat = $format;
    }

    public static function getAlternativeDateFormat(): ?string
    {
        return static::$alternativeDateFormat;
    }

    public static function getAlternativeDatetimeFormat(): ?string
    {
        return static::$alternativeDatetimeFormat;
    }

    public static function getAlternativeTimeFormat(): ?string
    {
        return static::$alternativeTimeFormat;
    }

    public function alternativeDateFormat(string $format): self
    {
        static::setAlternativeDateFormat($format);

        return $this;
    }

    public function alternativeDatetimeFormat(string $format): self
    {
        static::setAlternativeDatetimeFormat($format);

        return $this;
    }

    public function alternativeTimeFormat(string $format): self
    {
        static::setAlternativeTimeFormat($format);

        return $this;
    }
}
