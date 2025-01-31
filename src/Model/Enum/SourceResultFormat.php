<?php

namespace NicolasJoubert\GrabitBundle\Model\Enum;

enum SourceResultFormat: string
{
    case HTML = 'html';
    case JSON = 'json';
    case XML = 'xml';
}
