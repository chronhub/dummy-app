<?php

declare(strict_types=1);

namespace App\Chron\Projection\Filter;

use Illuminate\Database\Query\Builder;
use Storm\Contract\Projector\ProjectionQueryFilter;

final class FromIncludedPosition implements ProjectionQueryFilter
{
    private int $streamPosition;

    public function apply(): callable
    {
        return function (Builder $query): void {
            $query
                ->where('position', '>=', $this->streamPosition)
                ->orderBy('position');
        };
    }

    public function setStreamPosition(int $streamPosition): void
    {
        $this->streamPosition = $streamPosition;
    }
}
