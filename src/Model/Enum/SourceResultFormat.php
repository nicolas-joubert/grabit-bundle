<?php

declare(strict_types=1);

namespace NicolasJoubert\GrabitBundle\Model\Enum;

enum SourceResultFormat: string
{
    case HTML = 'html';
    case JSON = 'json';
    case XML = 'xml';
}
