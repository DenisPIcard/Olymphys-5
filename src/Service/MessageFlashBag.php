<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

class MessageFlashBag

{
    const TYPE_SUCCESS = 'success';
    const TYPE_ERROR = 'error';
    const TYPE_ALERT = 'alert';
    /**
     * @var FlashBagInterface
     */
    protected FlashBagInterface $flashBag;

    /**
     * @param FlashBagInterface $flashBag
     */
    public function __construct(FlashBagInterface $flashBag)
    {
        $this->flashBag = $flashBag;
    }

    /**
     * @param string $message
     * @return mixed
     */
    public function addSuccess(string $message): void
    {
        $this->flashBag->add('success', $message);
    }

    /**
     * @param string $message
     * @return mixed
     */
    public function addError(string $message): void
    {
        $this->flashBag->add(self::TYPE_ERROR, $message);
    }

    /**
     * @param string $message
     * @return mixed
     */
    public function addAlert(string $message): void
    {
        $this->flashBag->add('alert', $message);
    }

}

