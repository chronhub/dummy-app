<?php

declare(strict_types=1);

namespace App\Chron\Package\Chronicler\Contracts;

interface TransactionalEventableChronicler extends EventableChronicler, TransactionalChronicler
{
    /**
     * @var string
     */
    public const BEGIN_TRANSACTION_EVENT = 'begin_transaction';

    /**
     * @var string
     */
    public const COMMIT_TRANSACTION_EVENT = 'commit_transaction';

    /**
     * @var string
     */
    public const ROLLBACK_TRANSACTION_EVENT = 'rollback_transaction';
}
