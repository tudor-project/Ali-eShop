<?php

namespace App\Enums;

enum DataDeletionAction: string
{
    case Deleted = 'deleted';
    case Anonymized = 'anonymized';
}
