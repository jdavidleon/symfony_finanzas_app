<?php
/**
 * Created by PhpStorm.
 * User: jleon
 * Date: 23/01/2019
 * Time: 4:20 PM
 */

namespace App\Util;


use DateTime;
use DateTimeInterface;
use InvalidArgumentException;
use Gedmo\Mapping\Annotation as Gedmo;

trait TimestampAbleEntity
{
    /**
     * @var DateTimeInterface
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime", nullable=false)
     */
    protected $createdAt;

    /**
     * @var DateTimeInterface
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $updatedAt;

    /**
     * @var DateTimeInterface
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $deletedAt;

    public function __construct()
    {
        $this->createdAt = new DateTime('now');
    }

    /**
     * Sets createdAt.
     *
     * @param  DateTimeInterface $createdAt
     * @return $this
     */
    public function setCreatedAt(DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Returns createdAt.
     *
     * @return DateTimeInterface
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Sets updatedAt.
     *
     * @return void
     */
    private function setUpdatedAt()
    {
        throw new InvalidArgumentException("Este valor no puede ser establecido manualmente");
    }

    /**
     * Returns updatedAt.
     *
     * @return DateTimeInterface
     */
    public function getUpdatedAt(): ?DateTimeInterface
    {
        return $this->updatedAt;
    }

    /**
     * @return DateTimeInterface|null
     */
    public function getDeletedAt(): ?DateTimeInterface
    {
        return $this->deletedAt;
    }

    /**
     * @param DateTimeInterface $deletedAt
     * @return TimestampAbleEntity
     */
    public function setDeletedAt(DateTimeInterface $deletedAt): self
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }
}