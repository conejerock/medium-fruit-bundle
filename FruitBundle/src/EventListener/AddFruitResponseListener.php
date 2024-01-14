<?php
declare(strict_types=1);

namespace BasketFruit\FruitBundle\EventListener;

use Symfony\Component\HttpKernel\Event\ResponseEvent;

class AddFruitResponseListener
{
    const HEADER_KEY = 'X-Random-Fruit';
    const FRUITS = [
        "apple", "banana", "orange",
        "grapes", "strawberry", "watermelon",
        "pineapple", "mango", "blueberry"
    ];

    public function __invoke(ResponseEvent $event): void
    {
        $response = $event->getResponse();
        $response->headers->set(self::HEADER_KEY, self::FRUITS[array_rand(self::FRUITS)]);
    }
}
