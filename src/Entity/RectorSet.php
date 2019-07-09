<?php declare (strict_types=1);

namespace Rector\RectorCI\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Entity
 */
class RectorSet
{
    /**
     * @var UuidInterface
     * @ORM\Id()
     * @ORM\Column(type="uuid")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column()
     */
    private $name;

    /**
     * @var string
     * @ORM\Column()
     */
    private $title;


    public function __construct(
        UuidInterface $id,
        string $name,
        string $title
    )
    {
        $this->id = $id;
        $this->name = $name;
        $this->title = $title;
    }


    public function getId(): UuidInterface
    {
        return $this->id;
    }


    public function getName(): string
    {
        return $this->name;
    }


    public function getTitle(): string
    {
        return $this->title;
    }
}
