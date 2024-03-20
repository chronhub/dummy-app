<?php

declare(strict_types=1);

namespace App\Chron\Projection;

use App\Chron\Projection\Filter\FromIncludedPosition;
use App\Chron\Projection\Filter\FromIncludedPositionWithLimit;
use Storm\Contract\Projector\LoadLimiterProjectionQueryFilter;
use Storm\Contract\Projector\ProjectionQueryFilter;
use Storm\Contract\Projector\ProjectionQueryScope;

final class ConnectionQueryScope implements ProjectionQueryScope
{
    public function fromIncludedPosition(): ProjectionQueryFilter
    {
        return new FromIncludedPosition();
    }

    public function fromIncludedPositionWithLimit(): LoadLimiterProjectionQueryFilter
    {
        return new FromIncludedPositionWithLimit();
    }
}
