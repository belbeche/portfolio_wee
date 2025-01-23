<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Translatable\Entity\MappedSuperclass\AbstractTranslation;

/**
 * @ORM\Table(name="ext_translations", indexes={
 *      @ORM\Index(name="lookup_unique_idx", columns={"locale", "object_class", "field", "foreign_key"})
 * })
 * @ORM\Entity(repositoryClass="Gedmo\Translatable\Entity\Repository\TranslationRepository")
 */
class Translation extends AbstractTranslation
{
    // This entity is already implemented by Gedmo. No additional code is needed.
}
