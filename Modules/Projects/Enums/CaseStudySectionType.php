<?php

declare(strict_types=1);

namespace Modules\Projects\Enums;

enum CaseStudySectionType: string
{
    case Context = 'context';
    case Problem = 'problem';
    case Approach = 'approach';
    case Architecture = 'architecture';
    case Contribution = 'contribution';
    case Results = 'results';
    case Metrics = 'metrics';
    case Lessons = 'lessons';
}
