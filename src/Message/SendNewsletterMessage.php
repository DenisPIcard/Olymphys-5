<?php

namespace App\Message;

final class SendNewsletterMessage
{
    /*
     * Add whatever properties & methods you need to hold the
     * data for this message class.
     */
    private int $userId;
    private int $newsId;

    public function __construct(int $userId, int $newsId)
    {
        $this->userId = $userId;
        $this->newsId = $newsId;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getNewsId(): int
    {
        return $this->newsId;
    }
}
