<?php

namespace NicolasJoubert\GrabitBundle\Model\Enum;

use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

enum SourceProxy: string implements TranslatableInterface
{
    case NONE = 'none';
    case SQUID = 'squid';
    case FLARESOLVERR = 'flaresolverr';

    public function trans(TranslatorInterface $translator, ?string $locale = null): string
    {
        return $translator->trans('enum.source_proxy.'.$this->value, domain: 'GrabitBundle', locale: $locale);
    }
}
