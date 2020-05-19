<?php

namespace Mwerkzeug;

use MwStaticText;
use SilverStripe\i18n\Messages\MessageProvider;
use MwLang;

class Mw_i18nMessageProvider implements MessageProvider
{

/**
 * Localise this message
 *
 * @param  string $entity Identifier for this message in Namespace.key format
 * @param  string $default Default message
 * @param  array $injection List of injection variables
 * @return string Localised string
 */

    public function translate($entity, $default = null, $injection)
    {

        if ($default==null || is_array($default)) {
            return MwLang::get($entity);
        }

        if ($default=="_DEFAULTSTRING_" || $default=="_DEFAULTSTRING_") {
            $default="";
        }

        return MwStaticText::translate($entity, $default, $injection);
    }

    /**
     * Pluralise a message
     *
     * @param  string $entity Identifier for this message in Namespace.key format
     * @param  array|string $default Default message with pipe-separated delimiters, or array
     * @param  array $injection List of injection variables
     * @param  int $count Number to pluralise against
     * @return string Localised string
     */
    public function pluralise($entity, $default, $injection, $count)
    {
        // TODO: Implement pluralise() method.
    }
}
