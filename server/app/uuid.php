<?php

/**
 * credit: https://qiita.com/ucan-lab/items/5043e362f13ff73cc0c7
 */

declare(strict_types=1);

/**
 * UUID version 4
 */
class UuidV4Factory
{
    const PATTERN = 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx';

    /**
     * UUID Generation
     * @return string
     * @throws \Exception
     */
    public static function generate(): string
    {
        $chars = str_split(self::PATTERN);

        foreach ($chars as $i => $char) {
            if ($char === 'x') {
                $chars[$i] = dechex(random_int(0, 15));
            } elseif ($char === 'y') {
                $chars[$i] = dechex(random_int(8, 11));
            }
        }

        return implode('', $chars);
    }
}